<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Core\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionService = app(PermissionService::class);
        
        // Create basic permissions and roles
        $this->createBasicRolesAndPermissions();
    }

    /** @test */
    public function test_role_based_permissions_only()
    {
        // Create user with admin role
        $user = User::factory()->create();
        $adminRole = Role::findByName('admin');
        $user->assignRole($adminRole);

        // Test that user has permissions through role only
        $this->assertTrue($this->permissionService->userHasPermission($user, 'users.view'));
        
        // Test that user has no direct permissions
        $this->assertEquals(0, $user->getDirectPermissions()->count());
        
        // Test that user gets permissions via roles
        $this->assertGreaterThan(0, $user->getPermissionsViaRoles()->count());

        echo "✅ Role-based permissions test passed\n";
    }

    /** @test */
    public function test_department_scoped_permissions()
    {
        // Create department and manager
        $department = Department::factory()->create(['name' => 'IT Department']);
        $manager = User::factory()->create(['department_id' => $department->id]);
        $department->update(['manager_id' => $manager->id]);
        
        // Assign department manager role
        $managerRole = Role::findByName('department-manager');
        $manager->assignRole($managerRole);

        // Create employee in same department
        $employee = User::factory()->create(['department_id' => $department->id]);
        $userRole = Role::findByName('user');
        $employee->assignRole($userRole);

        // Test that manager can manage users in their department
        $this->assertTrue($this->permissionService->canManageUser($manager, $employee));
        
        // Test scoped permissions
        $this->assertTrue($this->permissionService->userHasScopedPermission($manager, 'users.view', $department->id));

        echo "✅ Department-scoped permissions test passed\n";
    }

    /** @test */
    public function test_accessible_departments()
    {
        // Create departments
        $itDept = Department::factory()->create(['name' => 'IT']);
        $hrDept = Department::factory()->create(['name' => 'HR']);
        
        // Create manager for IT department
        $manager = User::factory()->create(['department_id' => $itDept->id]);
        $itDept->update(['manager_id' => $manager->id]);
        $manager->assignRole('department-manager');

        // Test accessible departments
        $accessibleDepts = $this->permissionService->getUserAccessibleDepartments($manager);
        
        $this->assertTrue($accessibleDepts->contains('id', $itDept->id));
        $this->assertFalse($accessibleDepts->contains('id', $hrDept->id));

        echo "✅ Accessible departments test passed\n";
    }

    /** @test */
    public function test_super_admin_has_all_permissions()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        // Test that super admin has all permissions
        $this->assertTrue($this->permissionService->userHasPermission($superAdmin, 'users.view'));
        $this->assertTrue($this->permissionService->userHasPermission($superAdmin, 'users.delete'));
        $this->assertTrue($this->permissionService->userHasPermission($superAdmin, 'system.maintenance'));

        echo "✅ Super admin permissions test passed\n";
    }

    /** @test */
    public function test_direct_permissions_removed()
    {
        $user = User::factory()->create();
        
        // Try to assign direct permission (should be removed by service)
        $permission = Permission::findByName('users.view');
        $user->givePermissionTo($permission);
        
        // Use service to assign role (should remove direct permissions)
        $this->permissionService->assignRoleToUser($user, 'user');
        
        // Verify direct permissions are removed
        $this->assertEquals(0, $user->fresh()->getDirectPermissions()->count());
        
        echo "✅ Direct permissions removal test passed\n";
    }

    protected function createBasicRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'departments.view', 'system.maintenance'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(['users.view', 'users.create', 'users.edit', 'departments.view']);

        $departmentManager = Role::firstOrCreate(['name' => 'department-manager']);
        $departmentManager->syncPermissions(['users.view', 'users.create', 'users.edit', 'departments.view']);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions(['users.view']);
    }

    public function run_all_tests()
    {
        echo "🧪 اختبار نظام الصلاحيات الجديد...\n\n";
        
        try {
            $this->test_role_based_permissions_only();
            $this->test_department_scoped_permissions();
            $this->test_accessible_departments();
            $this->test_super_admin_has_all_permissions();
            $this->test_direct_permissions_removed();
            
            echo "\n🎉 جميع الاختبارات نجحت! النظام يعمل بشكل صحيح.\n";
            
        } catch (\Exception $e) {
            echo "\n❌ فشل في الاختبار: " . $e->getMessage() . "\n";
        }
    }
}
