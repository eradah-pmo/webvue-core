<?php

namespace Tests\Feature\Modules\Users;

use Tests\TestCase;
use App\Modules\Users\Services\UserFilterService;
use App\Modules\Users\Models\User;
use App\Modules\Roles\Models\Role;
use App\Modules\Departments\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserFilterService $filterService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filterService = app(UserFilterService::class);
    }

    /** @test */
    public function it_can_get_filter_options()
    {
        // Create test data
        $role = Role::factory()->create(['name' => 'test-role']);
        $department = Department::factory()->create(['name' => 'test-department']);
        
        $filterOptions = $this->filterService->getFilterOptions();
        
        $this->assertIsArray($filterOptions);
        $this->assertArrayHasKey('roles', $filterOptions);
        $this->assertArrayHasKey('departments', $filterOptions);
        $this->assertArrayHasKey('statuses', $filterOptions);
        
        $this->assertContains($role->id, collect($filterOptions['roles'])->pluck('id')->toArray());
        $this->assertContains($department->id, collect($filterOptions['departments'])->pluck('id')->toArray());
    }

    /** @test */
    public function it_can_filter_users_by_search_term()
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        
        $filters = ['search' => 'John'];
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertTrue($results->contains($user1));
        $this->assertFalse($results->contains($user2));
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $role1 = Role::factory()->create(['name' => 'manager']);
        $role2 = Role::factory()->create(['name' => 'editor']);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $user1->assignRole($role1);
        $user2->assignRole($role2);
        
        $filters = ['role_id' => $role1->id];
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertTrue($results->contains($user1));
        $this->assertFalse($results->contains($user2));
    }

    /** @test */
    public function it_can_filter_users_by_department()
    {
        $department1 = Department::factory()->create();
        $department2 = Department::factory()->create();
        
        $user1 = User::factory()->create(['department_id' => $department1->id]);
        $user2 = User::factory()->create(['department_id' => $department2->id]);
        
        $filters = ['department_id' => $department1->id];
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertTrue($results->contains($user1));
        $this->assertFalse($results->contains($user2));
    }

    /** @test */
    public function it_can_filter_users_by_status()
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => false]);
        
        $filters = ['status' => 'active'];
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertTrue($results->contains($user1));
        $this->assertFalse($results->contains($user2));
        
        $filters = ['status' => 'inactive'];
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertFalse($results->contains($user1));
        $this->assertTrue($results->contains($user2));
    }

    /** @test */
    public function it_can_paginate_filtered_users()
    {
        // Create 25 users
        User::factory()->count(25)->create();
        
        $perPage = 10;
        $paginatedUsers = $this->filterService->getPaginatedUsers([], $perPage);
        
        $this->assertEquals($perPage, $paginatedUsers->perPage());
        $this->assertEquals(25, $paginatedUsers->total());
        $this->assertEquals(3, $paginatedUsers->lastPage());
    }

    /** @test */
    public function it_can_combine_multiple_filters()
    {
        $department = Department::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        
        $matchingUser = User::factory()->create([
            'name' => 'John Admin',
            'department_id' => $department->id,
            'is_active' => true
        ]);
        $matchingUser->assignRole($role);
        
        $nonMatchingUser1 = User::factory()->create([
            'name' => 'John User',
            'department_id' => $department->id,
            'is_active' => true
        ]);
        
        $nonMatchingUser2 = User::factory()->create([
            'name' => 'Admin Bob',
            'is_active' => true
        ]);
        $nonMatchingUser2->assignRole($role);
        
        $filters = [
            'search' => 'John',
            'role_id' => $role->id,
            'department_id' => $department->id,
            'status' => 'active'
        ];
        
        $results = $this->filterService->getFilteredUsers($filters);
        
        $this->assertTrue($results->contains($matchingUser));
        $this->assertFalse($results->contains($nonMatchingUser1));
        $this->assertFalse($results->contains($nonMatchingUser2));
    }
}
