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

class UsersControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $manager;
    protected $user;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions first
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);
        
        // Assign permissions
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['users.view', 'users.create', 'users.edit', 'users.delete']);
        $managerRole->givePermissionTo(['users.view', 'users.create', 'users.edit']);
        
        // Create department
        $this->department = Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'active' => true,
        ]);
        
        // Create test users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->manager = User::factory()->create([
            'department_id' => $this->department->id
        ]);
        $this->manager->assignRole('manager');
        
        $this->user = User::factory()->create([
            'department_id' => $this->department->id
        ]);
        $this->user->assignRole('user');
    }

    /** @test */
    public function super_admin_can_view_users_index()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn($page) => 
                $page->component('Users/Index')
                    ->has('users')
            );
    }

    /** @test */
    public function unauthorized_user_cannot_view_users_index()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        Storage::fake('public');
        
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '+1234567890',
            'department_id' => $this->department->id,
            'active' => true,
            'roles' => ['user'],
        ];

        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /** @test */
    public function user_creation_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [])
            ->assertSessionHasErrors([
                'first_name',
                'last_name', 
                'email',
                'password',
            ]);
    }

    /** @test */
    public function user_creation_validates_unique_email()
    {
        $existingUser = User::factory()->create();
        
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $existingUser->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_creation_validates_password_strength()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ];

        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $testUser = User::factory()->create();
        
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '+0987654321',
            'active' => false,
        ];

        $this->actingAs($this->admin)
            ->put(route('users.update', $testUser), $updateData)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'active' => false,
        ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $testUser = User::factory()->create();
        
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $testUser))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $testUser->id,
        ]);
    }

    /** @test */
    public function admin_can_toggle_user_status()
    {
        $testUser = User::factory()->create(['active' => true]);
        
        $this->actingAs($this->admin)
            ->post(route('users.toggle-status', $testUser))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'active' => false,
        ]);
    }

    /** @test */
    public function user_can_view_own_profile()
    {
        $this->actingAs($this->user)
            ->get(route('users.show', $this->user))
            ->assertOk()
            ->assertInertia(fn($page) => 
                $page->component('Users/Show')
                    ->has('user')
            );
    }

    /** @test */
    public function user_cannot_view_other_user_profile_without_permission()
    {
        $otherUser = User::factory()->create();
        
        $this->actingAs($this->user)
            ->get(route('users.show', $otherUser))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_view_users_in_same_department()
    {
        $departmentUser = User::factory()->create([
            'department_id' => $this->department->id
        ]);
        
        $this->actingAs($this->manager)
            ->get(route('users.show', $departmentUser))
            ->assertOk();
    }

    /** @test */
    public function file_upload_validation_works()
    {
        Storage::fake('public');
        
        // Test valid image upload
        $validImage = UploadedFile::fake()->image('avatar.jpg', 100, 100)->size(1024);
        
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'avatar' => $validImage,
        ];

        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData)
            ->assertRedirect(route('users.index'));

        // Test oversized file
        $oversizedFile = UploadedFile::fake()->image('large.jpg')->size(15000); // 15MB
        
        $userData['avatar'] = $oversizedFile;
        
        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData)
            ->assertSessionHasErrors(['avatar']);
    }

    /** @test */
    public function activity_is_logged_on_user_creation()
    {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->actingAs($this->admin)
            ->post(route('users.store'), $userData);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->admin->id,
            'subject_type' => User::class,
        ]);
    }
} 