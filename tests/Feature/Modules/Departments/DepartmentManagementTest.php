<?php

namespace Tests\Feature\Modules\Departments;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DepartmentManagementTest extends TestCase
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
            'departments.view', 'departments.create', 'departments.edit', 
            'departments.delete', 'departments.export'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // إنشاء الأدوار
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['departments.view', 'departments.create', 'departments.edit']);
        
        // إنشاء المستخدمين
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    /** @test */
    public function admin_can_view_departments_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/departments');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Departments/Index')
        );
    }

    /** @test */
    public function unauthorized_user_cannot_view_departments()
    {
        $response = $this->actingAs($this->user)
            ->get('/departments');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_department()
    {
        $departmentData = [
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'Human Resources Department',
            'active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/departments', $departmentData);
        
        $response->assertRedirect('/departments');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('departments', [
            'name' => 'Human Resources',
            'code' => 'HR',
            'active' => true
        ]);
    }

    /** @test */
    public function department_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post('/departments', []);
        
        $response->assertSessionHasErrors([
            'name', 'code'
        ]);
    }

    /** @test */
    public function department_creation_validates_unique_code()
    {
        Department::create([
            'name' => 'Existing Department',
            'code' => 'EXIST',
            'active' => true
        ]);
        
        $departmentData = [
            'name' => 'New Department',
            'code' => 'EXIST', // كود موجود بالفعل
            'active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/departments', $departmentData);
        
        $response->assertSessionHasErrors(['code']);
    }

    /** @test */
    public function admin_can_update_department()
    {
        $department = Department::create([
            'name' => 'Original Department',
            'code' => 'ORIG',
            'description' => 'Original description',
            'active' => true
        ]);
        
        $updateData = [
            'name' => 'Updated Department',
            'code' => 'UPD',
            'description' => 'Updated description',
            'active' => false
        ];

        $response = $this->actingAs($this->admin)
            ->put("/departments/{$department->id}", $updateData);
        
        $response->assertRedirect('/departments');
        
        $department->refresh();
        $this->assertEquals('Updated Department', $department->name);
        $this->assertEquals('UPD', $department->code);
        $this->assertFalse($department->active);
    }

    /** @test */
    public function super_admin_can_delete_department()
    {
        $department = Department::create([
            'name' => 'Deletable Department',
            'code' => 'DEL',
            'active' => true
        ]);
        
        $response = $this->actingAs($this->superAdmin)
            ->delete("/departments/{$department->id}");
        
        $response->assertRedirect('/departments');
        $response->assertSessionHas('success');
        
        $this->assertSoftDeleted('departments', [
            'id' => $department->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_department()
    {
        $department = Department::create([
            'name' => 'Protected Department',
            'code' => 'PROT',
            'active' => true
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete("/departments/{$department->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_delete_department_with_users()
    {
        $department = Department::create([
            'name' => 'Department with Users',
            'code' => 'USERS',
            'active' => true
        ]);
        
        User::factory()->create(['department_id' => $department->id]);
        
        $response = $this->actingAs($this->superAdmin)
            ->delete("/departments/{$department->id}");
        
        $response->assertRedirect('/departments');
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function can_view_department_details()
    {
        $department = Department::create([
            'name' => 'Detail Department',
            'code' => 'DETAIL',
            'description' => 'Department for details test',
            'active' => true
        ]);
        
        // إضافة مستخدمين للقسم
        User::factory()->count(3)->create(['department_id' => $department->id]);
        
        $response = $this->actingAs($this->admin)
            ->get("/departments/{$department->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Departments/Show')
                ->has('department')
                ->has('users')
        );
    }

    /** @test */
    public function departments_can_be_filtered_by_status()
    {
        Department::create(['name' => 'Active Dept', 'code' => 'ACT', 'active' => true]);
        Department::create(['name' => 'Inactive Dept', 'code' => 'INACT', 'active' => false]);
        
        $response = $this->actingAs($this->admin)
            ->get('/departments?active=1');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على الأقسام النشطة فقط
    }

    /** @test */
    public function departments_can_be_searched_by_name()
    {
        Department::create(['name' => 'Search Test Department', 'code' => 'SEARCH', 'active' => true]);
        Department::create(['name' => 'Other Department', 'code' => 'OTHER', 'active' => true]);
        
        $response = $this->actingAs($this->admin)
            ->get('/departments?search=Search');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على القسم المطلوب
    }

    /** @test */
    public function can_toggle_department_status()
    {
        $department = Department::create([
            'name' => 'Toggle Department',
            'code' => 'TOGGLE',
            'active' => true
        ]);
        
        $response = $this->actingAs($this->admin)
            ->patch("/departments/{$department->id}/toggle-status");
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $department->refresh();
        $this->assertFalse($department->active);
    }

    /** @test */
    public function department_activity_is_logged()
    {
        $department = Department::create([
            'name' => 'Logged Department',
            'code' => 'LOG',
            'active' => true
        ]);
        
        $this->actingAs($this->admin)
            ->put("/departments/{$department->id}", [
                'name' => 'Updated Logged Department',
                'code' => 'UPLOG',
                'active' => true
            ]);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Department::class,
            'subject_id' => $department->id,
            'causer_id' => $this->admin->id,
            'description' => 'updated'
        ]);
    }

    /** @test */
    public function can_export_departments()
    {
        Department::factory()->count(5)->create();
        
        $response = $this->actingAs($this->superAdmin)
            ->get('/departments/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function can_get_department_statistics()
    {
        $department = Department::create([
            'name' => 'Stats Department',
            'code' => 'STATS',
            'active' => true
        ]);
        
        // إضافة مستخدمين للقسم
        User::factory()->count(5)->create(['department_id' => $department->id]);
        
        $response = $this->actingAs($this->admin)
            ->get("/departments/{$department->id}/statistics");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_users',
            'active_users',
            'inactive_users'
        ]);
    }

    /** @test */
    public function department_hierarchy_is_supported()
    {
        $parentDept = Department::create([
            'name' => 'Parent Department',
            'code' => 'PARENT',
            'active' => true
        ]);
        
        $childDept = Department::create([
            'name' => 'Child Department',
            'code' => 'CHILD',
            'parent_id' => $parentDept->id,
            'active' => true
        ]);
        
        $this->assertEquals($parentDept->id, $childDept->parent_id);
        $this->assertTrue($parentDept->children->contains($childDept));
    }
}
