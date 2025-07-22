<?php

namespace Tests\Unit\Services\Roles;

use Tests\TestCase;
use App\Modules\Roles\Models\Roles;
use App\Modules\Roles\Services\RolePermissionService;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;

class RolePermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RolePermissionService $permissionService;
    protected Roles $testRole;
    protected array $testPermissions = [];

    public function setUp(): void
    {
        parent::setUp();
        
        $this->permissionService = new RolePermissionService();
        
        // Create test permissions
        $this->createTestPermissions();
        
        // Create test role directly
        $this->testRole = new Roles();
        $this->testRole->name = 'Test Role';
        $this->testRole->guard_name = 'web';
        $this->testRole->save();
    }
    
    /**
     * Create test permissions for use in tests
     */
    private function createTestPermissions(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
        ];
        
        foreach ($permissions as $permission) {
            $this->testPermissions[] = Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    /**
     * Test getting all permissions
     */
    public function test_get_all_permissions()
    {
        $permissions = $this->permissionService->getAllPermissions();
        
        $this->assertCount(count($this->testPermissions), $permissions);
        $this->assertEquals('users.view', $permissions->first()->name);
    }
    
    /**
     * Test getting grouped permissions
     */
    public function test_get_grouped_permissions()
    {
        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        
        $this->assertIsArray($groupedPermissions);
        $this->assertArrayHasKey('users', $groupedPermissions);
        $this->assertArrayHasKey('roles', $groupedPermissions);
        
        // Check users group
        $this->assertEquals('Users', $groupedPermissions['users']['name']);
        $this->assertCount(4, $groupedPermissions['users']['permissions']);
        
        // Check roles group
        $this->assertEquals('Roles', $groupedPermissions['roles']['name']);
        $this->assertCount(2, $groupedPermissions['roles']['permissions']);
        
        // Check format of individual permissions
        $permission = $groupedPermissions['users']['permissions'][0];
        $this->assertArrayHasKey('id', $permission);
        $this->assertArrayHasKey('name', $permission);
        $this->assertArrayHasKey('display_name', $permission);
    }
    
    /**
     * Test syncing permissions to a role
     */
    public function test_sync_permissions()
    {
        // Mock authenticated user
        $this->mockAuthUser();
        
        // Get permission IDs
        $permissionIds = Permission::whereIn('name', ['users.view', 'users.create'])->pluck('id')->toArray();
        
        // Sync permissions
        $role = $this->permissionService->syncPermissions($this->testRole, $permissionIds);
        
        // Check role has correct permissions
        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
        $this->assertFalse($role->hasPermissionTo('users.edit'));
    }
    
    /**
     * Test adding permissions to a role
     */
    public function test_add_permissions()
    {
        // Mock authenticated user
        $this->mockAuthUser();
        
        // First add some initial permissions
        $initialPermissionIds = Permission::where('name', 'users.view')->pluck('id')->toArray();
        $this->testRole->syncPermissions($initialPermissionIds);
        $this->assertCount(1, $this->testRole->permissions);
        
        // Add more permissions
        $addPermissionIds = Permission::whereIn('name', ['users.create', 'users.edit'])->pluck('id')->toArray();
        $role = $this->permissionService->addPermissions($this->testRole, $addPermissionIds);
        
        // Check role now has all permissions
        $this->assertCount(3, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
        $this->assertTrue($role->hasPermissionTo('users.edit'));
    }
    
    /**
     * Test removing permissions from a role
     */
    public function test_remove_permissions()
    {
        // Mock authenticated user
        $this->mockAuthUser();
        
        // First add all permissions
        $allPermissionIds = Permission::whereIn('name', ['users.view', 'users.create', 'users.edit'])->pluck('id')->toArray();
        $this->testRole->syncPermissions($allPermissionIds);
        $this->assertCount(3, $this->testRole->permissions);
        
        // Remove some permissions
        $removePermissionIds = Permission::whereIn('name', ['users.create', 'users.edit'])->pluck('id')->toArray();
        $role = $this->permissionService->removePermissions($this->testRole, $removePermissionIds);
        
        // Check role now has only remaining permission
        $this->assertCount(1, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertFalse($role->hasPermissionTo('users.create'));
        $this->assertFalse($role->hasPermissionTo('users.edit'));
    }
    
    /**
     * Helper function to mock authenticated user
     */
    private function mockAuthUser()
    {
        $user = Mockery::mock(\App\Modules\Users\Models\User::class);
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn(1);
        
        return $user;
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
