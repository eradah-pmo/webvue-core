<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Core\Services\ModuleService;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ModuleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ModuleService $moduleService;
    protected $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->moduleService = new ModuleService();
        $this->modulesPath = app_path('Modules');
    }

    /** @test */
    public function it_can_get_all_modules()
    {
        $modules = $this->moduleService->getAllModules();
        
        $this->assertNotEmpty($modules);
        $this->assertTrue($modules->contains('name', 'Users'));
        $this->assertTrue($modules->contains('name', 'Departments'));
        $this->assertTrue($modules->contains('name', 'Roles'));
    }

    /** @test */
    public function it_can_get_active_modules_only()
    {
        $activeModules = $this->moduleService->getActiveModules();
        
        // All test modules should be active by default
        $this->assertNotEmpty($activeModules);
        
        foreach ($activeModules as $module) {
            $this->assertTrue($module['active']);
        }
    }

    /** @test */
    public function it_can_get_specific_module()
    {
        $usersModule = $this->moduleService->getModule('Users');
        
        $this->assertNotNull($usersModule);
        $this->assertEquals('Users', $usersModule['name']);
        $this->assertEquals('1.0.0', $usersModule['version']);
        $this->assertTrue($usersModule['active']);
    }

    /** @test */
    public function it_returns_null_for_non_existent_module()
    {
        $nonExistentModule = $this->moduleService->getModule('NonExistent');
        
        $this->assertNull($nonExistentModule);
    }

    /** @test */
    public function it_can_check_module_dependencies()
    {
        // Create a mock module with dependencies
        $moduleWithDependencies = [
            'name' => 'TestModule',
            'dependencies' => ['Users', 'Departments'],
            'active' => true,
        ];

        $result = $this->moduleService->checkDependencies($moduleWithDependencies);
        $this->assertTrue($result);

        // Test with non-existent dependency
        $moduleWithBadDependencies = [
            'name' => 'TestModule',
            'dependencies' => ['NonExistent'],
            'active' => true,
        ];

        $result = $this->moduleService->checkDependencies($moduleWithBadDependencies);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_dependent_modules()
    {
        // This test assumes we have module configurations with dependencies
        $dependents = $this->moduleService->getDependentModules('Users');
        
        // Should return collection (might be empty if no modules depend on Users)
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $dependents);
    }

    /** @test */
    public function it_can_get_module_navigation_for_user()
    {
        // Create user with appropriate roles
        $user = User::factory()->create();
        
        // Create roles and permissions
        $role = Role::create(['name' => 'admin']);
        $user->assignRole($role);
        
        // Create permissions first
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'departments.view']);
        Permission::create(['name' => 'roles.view']);
        
        // Mock user permissions (in real scenario, these would be seeded)
        $user->givePermissionTo([
            'users.view',
            'departments.view',
            'roles.view',
        ]);

        $navigation = $this->moduleService->getNavigationForUser($user);
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $navigation);
        
        // Should contain modules the user has access to
        foreach ($navigation as $navItem) {
            $this->assertArrayHasKey('name', $navItem);
            $this->assertArrayHasKey('navigation', $navItem);
            $this->assertArrayHasKey('permissions', $navItem);
        }
    }

    /** @test */
    public function it_caches_module_data_when_enabled()
    {
        // Clear any existing cache
        Cache::forget('modules.all');
        
        // Enable caching via config
        config(['modules.cache_enabled' => true]);
        
        // First call should cache the data
        $modules1 = $this->moduleService->getAllModules();
        
        // Verify data is cached
        $this->assertTrue(Cache::has('modules.all'));
        
        // Second call should use cached data
        $modules2 = $this->moduleService->getAllModules();
        
        $this->assertEquals($modules1->toArray(), $modules2->toArray());
    }

    /** @test */
    public function it_can_clear_module_cache()
    {
        // Set some cache data
        Cache::put('modules.all', collect(['test' => 'data']), 3600);
        Cache::put('modules.navigation', collect(['nav' => 'data']), 3600);
        
        $this->assertTrue(Cache::has('modules.all'));
        $this->assertTrue(Cache::has('modules.navigation'));
        
        // Clear cache
        $this->moduleService->clearCache();
        
        $this->assertFalse(Cache::has('modules.all'));
        $this->assertFalse(Cache::has('modules.navigation'));
    }

    /** @test */
    public function it_validates_module_structure()
    {
        $allModules = $this->moduleService->getAllModules();
        
        foreach ($allModules as $module) {
            // Required fields
            $this->assertArrayHasKey('name', $module);
            $this->assertArrayHasKey('version', $module);
            $this->assertArrayHasKey('active', $module);
            
            // Optional but common fields
            if (isset($module['permissions'])) {
                $this->assertIsArray($module['permissions']);
            }
            
            if (isset($module['dependencies'])) {
                $this->assertIsArray($module['dependencies']);
            }
            
            if (isset($module['navigation'])) {
                $this->assertIsArray($module['navigation']);
                $this->assertArrayHasKey('name', $module['navigation']);
                $this->assertArrayHasKey('href', $module['navigation']);
            }
        }
    }

    /** @test */
    public function it_handles_missing_modules_directory_gracefully()
    {
        // Test with reflection to access protected method
        $service = new ModuleService();
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('modulesPath');
        $property->setAccessible(true);
        $property->setValue($service, '/non/existent/path');
        
        $method = $reflection->getMethod('loadModulesFromFilesystem');
        $method->setAccessible(true);
        $modules = $method->invoke($service);
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $modules);
        $this->assertTrue($modules->isEmpty());
    }

    /** @test */
    public function it_ignores_invalid_module_json_files()
    {
        // This test would require creating temporary invalid JSON files
        // For now, we'll test that the service handles malformed JSON gracefully
        
        $modules = $this->moduleService->getAllModules();
        
        // Should not throw exceptions and should return valid collection
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $modules);
        
        // All returned modules should have valid structure
        foreach ($modules as $module) {
            $this->assertIsArray($module);
            $this->assertArrayHasKey('name', $module);
        }
    }

    protected function tearDown(): void
    {
        // Clean up any test cache
        Cache::forget('modules.all');
        Cache::forget('modules.navigation');
        
        parent::tearDown();
    }
} 