<?php

namespace Tests\Feature\Modules\Departments;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DepartmentsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions first
        $permissions = [
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'users.view', 'users.create', 'users.edit', 'users.delete'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        // Assign all permissions to super-admin
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['departments.view', 'departments.create', 'departments.edit', 'users.view']);
        
        // Create test users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        // Create test department
        $this->department = Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'description' => 'Information Technology Department',
            'active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_view_departments_index()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('departments.index'))
            ->assertOk()
            ->assertInertia(fn($page) => 
                $page->component('Departments/Index')
                    ->has('departments')
                    ->has('hierarchy')
            );
    }

    /** @test */
    public function unauthorized_user_cannot_view_departments_index()
    {
        $this->actingAs($this->user)
            ->get(route('departments.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_create_new_department()
    {
        $departmentData = [
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'Human Resources Department',
            'email' => 'hr@company.com',
            'phone' => '+1234567890',
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('departments.store'), $departmentData)
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'Human Resources',
            'code' => 'HR',
            'email' => 'hr@company.com',
        ]);
    }

    /** @test */
    public function department_creation_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('departments.store'), [])
            ->assertSessionHasErrors([
                'name',
                'code',
            ]);
    }

    /** @test */
    public function department_creation_validates_unique_code()
    {
        $departmentData = [
            'name' => 'Another IT Department',
            'code' => 'IT', // Same code as existing department
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('departments.store'), $departmentData)
            ->assertSessionHasErrors(['code']);
    }

    /** @test */
    public function admin_can_update_department()
    {
        $updateData = [
            'name' => 'Updated IT Department',
            'code' => 'IT-UPD',
            'description' => 'Updated description',
            'active' => false,
        ];

        $this->actingAs($this->admin)
            ->put(route('departments.update', $this->department), $updateData)
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'name' => 'Updated IT Department',
            'code' => 'IT-UPD',
            'active' => false,
        ]);
    }

    /** @test */
    public function admin_can_create_sub_department()
    {
        $subDepartmentData = [
            'name' => 'Software Development',
            'code' => 'SOFT-DEV',
            'parent_id' => $this->department->id,
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('departments.store'), $subDepartmentData)
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'name' => 'Software Development',
            'parent_id' => $this->department->id,
        ]);
    }

    /** @test */
    public function cannot_create_circular_parent_child_relationship()
    {
        // Create a sub-department
        $subDept = Department::create([
            'name' => 'Sub Department',
            'code' => 'SUB',
            'parent_id' => $this->department->id,
            'active' => true,
        ]);

        // Try to make the parent department a child of its sub-department
        $updateData = [
            'name' => $this->department->name,
            'code' => $this->department->code,
            'parent_id' => $subDept->id, // This should fail
        ];

        $this->actingAs($this->admin)
            ->put(route('departments.update', $this->department), $updateData)
            ->assertSessionHasErrors(['parent_id']);
    }

    /** @test */
    public function cannot_delete_department_with_users()
    {
        // Assign a user to the department
        User::factory()->create(['department_id' => $this->department->id]);

        $this->actingAs($this->admin)
            ->delete(route('departments.destroy', $this->department))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('error');

        // Department should still exist
        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
        ]);
    }

    /** @test */
    public function cannot_delete_department_with_sub_departments()
    {
        // Create a sub-department
        Department::create([
            'name' => 'Sub Department',
            'code' => 'SUB',
            'parent_id' => $this->department->id,
            'active' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('departments.destroy', $this->department))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('error');

        // Department should still exist
        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
        ]);
    }

    /** @test */
    public function can_delete_empty_department()
    {
        $emptyDepartment = Department::create([
            'name' => 'Empty Department',
            'code' => 'EMPTY',
            'active' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('departments.destroy', $emptyDepartment))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('departments', [
            'id' => $emptyDepartment->id,
        ]);
    }

    /** @test */
    public function admin_can_toggle_department_status()
    {
        $this->assertTrue($this->department->active);

        $this->actingAs($this->admin)
            ->post(route('departments.toggle-status', $this->department))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'active' => false,
        ]);
    }

    /** @test */
    public function admin_can_assign_manager_to_department()
    {
        $manager = User::factory()->create();

        $updateData = [
            'name' => $this->department->name,
            'code' => $this->department->code,
            'manager_id' => $manager->id,
        ];

        $this->actingAs($this->admin)
            ->put(route('departments.update', $this->department), $updateData)
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'manager_id' => $manager->id,
        ]);
    }

    /** @test */
    public function department_search_returns_correct_results()
    {
        Department::create([
            'name' => 'Marketing Department',
            'code' => 'MKT',
            'active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('departments.search', ['q' => 'Marketing']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Marketing Department']);
    }

    /** @test */
    public function department_hierarchy_is_maintained()
    {
        $parentDept = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'active' => true,
        ]);

        $childDept = Department::create([
            'name' => 'Software Engineering',
            'code' => 'SW-ENG',
            'parent_id' => $parentDept->id,
            'active' => true,
        ]);

        $grandChildDept = Department::create([
            'name' => 'Frontend Development',
            'code' => 'FE-DEV',
            'parent_id' => $childDept->id,
            'active' => true,
        ]);

        // Test hierarchy relationships
        $this->assertTrue($childDept->parent->is($parentDept));
        $this->assertTrue($grandChildDept->parent->is($childDept));
        $this->assertTrue($parentDept->children->contains($childDept));
        $this->assertTrue($childDept->children->contains($grandChildDept));
    }

    /** @test */
    public function activity_is_logged_on_department_creation()
    {
        $departmentData = [
            'name' => 'Test Department',
            'code' => 'TEST',
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('departments.store'), $departmentData);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->admin->id,
            'subject_type' => Department::class,
        ]);
    }

    /** @test */
    public function department_stats_are_calculated_correctly()
    {
        // Create users in department
        User::factory()->count(3)->create(['department_id' => $this->department->id]);
        
        // Create sub-departments
        Department::factory()->count(2)->create(['parent_id' => $this->department->id]);

        $this->actingAs($this->admin)
            ->get(route('departments.show', $this->department))
            ->assertOk()
            ->assertInertia(fn($page) => 
                $page->component('Departments/Show')
                    ->has('department')
                    ->has('stats')
                    ->where('stats.users_count', 3)
                    ->where('stats.children_count', 2)
            );
    }
} 