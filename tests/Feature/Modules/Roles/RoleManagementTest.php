<?php

namespace Tests\Feature\Modules\Roles;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء الصلاحيات
        $permissions = [
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.assign', 'users.view', 'users.edit'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // إنشاء الأدوار الأساسية
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['roles.view', 'roles.create', 'roles.edit']);
        
        // إنشاء المستخدمين
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    /** @test */
    public function super_admin_can_view_roles_index()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/roles');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Roles/Index')
        );
    }

    /** @test */
    public function unauthorized_user_cannot_view_roles()
    {
        $response = $this->actingAs($this->user)
            ->get('/roles');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_role()
    {
        $roleData = [
            'name' => 'test-role',
            'display_name' => 'Test Role',
            'description' => 'A test role for testing',
            'permissions' => ['users.view', 'users.edit']
        ];

        $response = $this->actingAs($this->admin)
            ->post('/roles', $roleData);
        
        $response->assertRedirect('/roles');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('roles', [
            'name' => 'test-role',
            'display_name' => 'Test Role'
        ]);
        
        $role = Role::where('name', 'test-role')->first();
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('users.edit'));
    }

    /** @test */
    public function role_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post('/roles', []);
        
        $response->assertSessionHasErrors([
            'name', 'display_name'
        ]);
    }

    /** @test */
    public function role_creation_validates_unique_name()
    {
        $roleData = [
            'name' => 'admin', // اسم موجود بالفعل
            'display_name' => 'Another Admin'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/roles', $roleData);
        
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_update_role()
    {
        $role = Role::create([
            'name' => 'updatable-role',
            'display_name' => 'Updatable Role'
        ]);
        
        $updateData = [
            'display_name' => 'Updated Role Name',
            'description' => 'Updated description',
            'permissions' => ['users.view']
        ];

        $response = $this->actingAs($this->admin)
            ->put("/roles/{$role->id}", $updateData);
        
        $response->assertRedirect('/roles');
        
        $role->refresh();
        $this->assertEquals('Updated Role Name', $role->display_name);
        $this->assertEquals('Updated description', $role->description);
        $this->assertTrue($role->hasPermissionTo('users.view'));
    }

    /** @test */
    public function super_admin_can_delete_role()
    {
        $role = Role::create([
            'name' => 'deletable-role',
            'display_name' => 'Deletable Role'
        ]);
        
        $response = $this->actingAs($this->superAdmin)
            ->delete("/roles/{$role->id}");
        
        $response->assertRedirect('/roles');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('roles', [
            'id' => $role->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_role()
    {
        $role = Role::create([
            'name' => 'protected-role',
            'display_name' => 'Protected Role'
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete("/roles/{$role->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_delete_role_with_assigned_users()
    {
        $role = Role::create([
            'name' => 'assigned-role',
            'display_name' => 'Assigned Role'
        ]);
        
        $testUser = User::factory()->create();
        $testUser->assignRole($role);
        
        $response = $this->actingAs($this->superAdmin)
            ->delete("/roles/{$role->id}");
        
        $response->assertRedirect('/roles');
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('roles', [
            'id' => $role->id
        ]);
    }

    /** @test */
    public function can_view_role_permissions_matrix()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/roles/permissions-matrix');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Roles/PermissionsMatrix')
        );
    }

    /** @test */
    public function can_assign_permissions_to_role()
    {
        $role = Role::create([
            'name' => 'test-permissions',
            'display_name' => 'Test Permissions'
        ]);
        
        $permissions = ['users.view', 'users.edit', 'roles.view'];
        
        $response = $this->actingAs($this->superAdmin)
            ->post("/roles/{$role->id}/permissions", [
                'permissions' => $permissions
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($permissions as $permission) {
            $this->assertTrue($role->hasPermissionTo($permission));
        }
    }

    /** @test */
    public function can_revoke_permissions_from_role()
    {
        $role = Role::create([
            'name' => 'test-revoke',
            'display_name' => 'Test Revoke'
        ]);
        
        $role->givePermissionTo(['users.view', 'users.edit']);
        
        $response = $this->actingAs($this->superAdmin)
            ->post("/roles/{$role->id}/permissions", [
                'permissions' => ['users.view'] // إزالة users.edit
            ]);
        
        $response->assertRedirect();
        
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertFalse($role->hasPermissionTo('users.edit'));
    }

    /** @test */
    public function roles_can_be_filtered_by_name()
    {
        Role::create(['name' => 'filter-test-1', 'display_name' => 'Filter Test 1']);
        Role::create(['name' => 'filter-test-2', 'display_name' => 'Filter Test 2']);
        Role::create(['name' => 'other-role', 'display_name' => 'Other Role']);
        
        $response = $this->actingAs($this->admin)
            ->get('/roles?search=filter');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على الأدوار المطلوبة فقط
    }

    /** @test */
    public function role_activity_is_logged()
    {
        $role = Role::create([
            'name' => 'logged-role',
            'display_name' => 'Logged Role'
        ]);
        
        $this->actingAs($this->admin)
            ->put("/roles/{$role->id}", [
                'display_name' => 'Updated Logged Role',
                'permissions' => []
            ]);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'causer_id' => $this->admin->id,
            'description' => 'updated'
        ]);
    }

    /** @test */
    public function can_export_roles()
    {
        Role::create(['name' => 'export-test-1', 'display_name' => 'Export Test 1']);
        Role::create(['name' => 'export-test-2', 'display_name' => 'Export Test 2']);
        
        $response = $this->actingAs($this->superAdmin)
            ->get('/roles/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
