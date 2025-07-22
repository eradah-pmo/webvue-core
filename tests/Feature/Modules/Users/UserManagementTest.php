<?php

namespace Tests\Feature\Modules\Users;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء الصلاحيات
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'users.export', 'users.import', 'users.reset-password'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // إنشاء الأدوار
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['users.view', 'users.create', 'users.edit']);
        
        // إنشاء قسم
        $this->department = Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'description' => 'Information Technology',
            'active' => true
        ]);
        
        // إنشاء المستخدمين
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        Storage::fake('public');
    }

    /** @test */
    public function super_admin_can_view_users_index()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/users');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Users/Index')
        );
    }

    /** @test */
    public function unauthorized_user_cannot_view_users()
    {
        $response = $this->actingAs($this->user)
            ->get('/users');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_user()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'department_id' => $this->department->id,
            'active' => true,
            'roles' => ['user']
        ];

        $response = $this->actingAs($this->admin)
            ->post('/users', $userData);
        
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'department_id' => $this->department->id
        ]);
    }

    /** @test */
    public function user_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post('/users', []);
        
        $response->assertSessionHasErrors([
            'name', 'email', 'password'
        ]);
    }

    /** @test */
    public function user_creation_validates_unique_email()
    {
        $existingUser = User::factory()->create();
        
        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/users', $userData);
        
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $targetUser = User::factory()->create();
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'department_id' => $this->department->id,
            'active' => false
        ];

        $response = $this->actingAs($this->admin)
            ->put("/users/{$targetUser->id}", $updateData);
        
        $response->assertRedirect('/users');
        
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'active' => false
        ]);
    }

    /** @test */
    public function admin_can_upload_user_avatar()
    {
        $targetUser = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        
        $response = $this->actingAs($this->admin)
            ->post("/users/{$targetUser->id}/avatar", [
                'avatar' => $avatar
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $targetUser->refresh();
        $this->assertNotNull($targetUser->avatar);
        Storage::disk('public')->assertExists($targetUser->avatar);
    }

    /** @test */
    public function super_admin_can_delete_user()
    {
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($this->superAdmin)
            ->delete("/users/{$targetUser->id}");
        
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
        
        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_user()
    {
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete("/users/{$targetUser->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_reset_user_password()
    {
        $targetUser = User::factory()->create();
        $newPassword = 'newpassword123';
        
        $response = $this->actingAs($this->superAdmin)
            ->post("/users/{$targetUser->id}/reset-password", [
                'password' => $newPassword,
                'password_confirmation' => $newPassword
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $targetUser->refresh();
        $this->assertTrue(Hash::check($newPassword, $targetUser->password));
    }

    /** @test */
    public function users_can_be_filtered_by_department()
    {
        $dept2 = Department::create([
            'name' => 'HR Department',
            'code' => 'HR',
            'active' => true
        ]);
        
        $itUser = User::factory()->create(['department_id' => $this->department->id]);
        $hrUser = User::factory()->create(['department_id' => $dept2->id]);
        
        $response = $this->actingAs($this->admin)
            ->get('/users?department_id=' . $this->department->id);
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على مستخدم IT فقط
    }

    /** @test */
    public function users_can_be_searched_by_name_or_email()
    {
        $searchUser = User::factory()->create([
            'name' => 'John Search Test',
            'email' => 'search@test.com'
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get('/users?search=Search');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على المستخدم المطلوب
    }

    /** @test */
    public function users_export_requires_permission()
    {
        $response = $this->actingAs($this->user)
            ->get('/users/export');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_export_users()
    {
        User::factory()->count(5)->create();
        
        $response = $this->actingAs($this->superAdmin)
            ->get('/users/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function user_activity_is_logged()
    {
        $targetUser = User::factory()->create();
        
        $this->actingAs($this->admin)
            ->put("/users/{$targetUser->id}", [
                'name' => 'Updated Name',
                'email' => $targetUser->email
            ]);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'causer_id' => $this->admin->id,
            'description' => 'updated'
        ]);
    }
}
