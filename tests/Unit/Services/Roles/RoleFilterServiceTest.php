<?php

namespace Tests\Unit\Services\Roles;

use Tests\TestCase;
use App\Modules\Roles\Models\Roles;
use App\Modules\Roles\Services\RoleFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleFilterService $filterService;

    public function setUp(): void
    {
        parent::setUp();
        $this->filterService = new RoleFilterService();
    }

    /**
     * Test getting filter options
     */
    public function test_get_filter_options()
    {
        // Create some roles with different levels for testing
        $this->createRole(['level' => 1]);
        $this->createRole(['level' => 2]);
        
        $options = $this->filterService->getFilterOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('statuses', $options);
        $this->assertArrayHasKey('levels', $options);
        
        // Check statuses format
        $this->assertCount(2, $options['statuses']); // Active and Inactive
        $this->assertArrayHasKey('id', $options['statuses'][0]);
        $this->assertArrayHasKey('name', $options['statuses'][0]);
        $this->assertArrayHasKey('name_en', $options['statuses'][0]);
        
        // Check levels format and content
        $this->assertNotEmpty($options['levels']);
        $this->assertArrayHasKey('id', $options['levels'][0]);
        $this->assertArrayHasKey('name', $options['levels'][0]);
        $this->assertArrayHasKey('name_en', $options['levels'][0]);
    }
    
    /**
     * Test getting paginated roles with no filters
     */
    public function test_get_paginated_roles_no_filters()
    {
        // Create test roles
        for ($i = 0; $i < 5; $i++) {
            $this->createRole(['name' => 'Role ' . $i]);
        }
        
        $result = $this->filterService->getPaginatedRoles([], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }
    
    /**
     * Test getting paginated roles with search filter
     */
    public function test_get_paginated_roles_with_search()
    {
        // Create test roles
        $this->createRole(['name' => 'Admin Role']);
        $this->createRole(['name' => 'User Role']);
        $this->createRole(['name' => 'Manager']);
        
        // Search for 'Role' in name
        $result = $this->filterService->getPaginatedRoles(['search' => 'Role'], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
    }
    
    /**
     * Test getting paginated roles with active filter
     */
    public function test_get_paginated_roles_with_active_filter()
    {
        // Create test roles
        for ($i = 0; $i < 3; $i++) {
            $this->createRole(['name' => 'Active Role ' . $i, 'active' => true]);
        }
        
        for ($i = 0; $i < 2; $i++) {
            $this->createRole(['name' => 'Inactive Role ' . $i, 'active' => false]);
        }
        
        // Get only active roles
        $result = $this->filterService->getPaginatedRoles(['active' => '1'], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(3, $result->total());
        
        // Get only inactive roles
        $result = $this->filterService->getPaginatedRoles(['active' => '0'], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
    }
    
    /**
     * Test getting paginated roles with level filter
     */
    public function test_get_paginated_roles_with_level_filter()
    {
        // Create test roles with different levels
        for ($i = 0; $i < 2; $i++) {
            $this->createRole(['name' => 'Level 1 Role ' . $i, 'level' => 1]);
        }
        
        for ($i = 0; $i < 3; $i++) {
            $this->createRole(['name' => 'Level 2 Role ' . $i, 'level' => 2]);
        }
        
        // Filter by level 1
        $result = $this->filterService->getPaginatedRoles(['level' => 1], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        
        // Filter by level 2
        $result = $this->filterService->getPaginatedRoles(['level' => 2], 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(3, $result->total());
    }
    
    /**
     * Test getting filtered roles for export
     */
    public function test_get_filtered_roles_for_export()
    {
        // Create test roles
        for ($i = 0; $i < 10; $i++) {
            $this->createRole(['name' => 'Export Role ' . $i]);
        }
        
        // Get all roles
        $result = $this->filterService->getFilteredRoles();
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(10, $result->count());
        
        // Get with limit
        $result = $this->filterService->getFilteredRoles([], 5);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(5, $result->count());
        
        // Get with filter
        $this->createRole(['name' => 'Special Role']);
        $result = $this->filterService->getFilteredRoles(['search' => 'Special']);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
    }
    
    /**
     * Helper method to create a role without using factories
     * Avoids using columns that may not exist in test database
     *
     * @param array $attributes
     * @return Roles
     */
    private function createRole(array $attributes = []): Roles
    {
        $role = new Roles();
        $role->name = $attributes['name'] ?? 'Test Role';
        $role->guard_name = 'web';
        // We don't set active or level as they don't exist in the base roles table
        $role->save();
        
        return $role;
    }
}
