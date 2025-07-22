<?php

namespace Tests\Unit\Services\Departments;

use Tests\TestCase;
use App\Modules\Departments\Services\DepartmentsService;
use App\Modules\Departments\Models\Department;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentsServiceTest extends TestCase
{
    use RefreshDatabase;

    private DepartmentsService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DepartmentsService();
        
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_create_department()
    {
        $data = [
            'name' => 'IT Department',
            'description' => 'Information Technology Department',
            'manager_id' => $this->user->id,
            'active' => true
        ];

        $department = $this->service->create($data);

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('IT Department', $department->name);
        $this->assertEquals($this->user->id, $department->manager_id);
        $this->assertTrue($department->active);
    }

    public function test_update_department()
    {
        $department = Department::create([
            'name' => 'Old Name',
            'description' => 'Old Description',
            'active' => true
        ]);

        $updateData = [
            'name' => 'New Name',
            'description' => 'New Description'
        ];

        $updatedDepartment = $this->service->update($department, $updateData);

        $this->assertEquals('New Name', $updatedDepartment->name);
        $this->assertEquals('New Description', $updatedDepartment->description);
    }

    public function test_delete_department()
    {
        $department = Department::create([
            'name' => 'Test Department',
            'active' => true
        ]);

        $result = $this->service->delete($department);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_delete_department_with_children_throws_exception()
    {
        $parent = Department::create([
            'name' => 'Parent Department',
            'active' => true
        ]);

        $child = Department::create([
            'name' => 'Child Department',
            'parent_id' => $parent->id,
            'active' => true
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete department with child departments');

        $this->service->delete($parent);
    }

    public function test_get_department_hierarchy()
    {
        $parent = Department::create([
            'name' => 'Parent Department',
            'active' => true
        ]);

        $child = Department::create([
            'name' => 'Child Department',
            'parent_id' => $parent->id,
            'active' => true
        ]);

        $hierarchy = $this->service->getDepartmentHierarchy();

        $this->assertIsArray($hierarchy);
        $this->assertCount(1, $hierarchy); // Only parent should be at root level
        $this->assertEquals('Parent Department', $hierarchy[0]['name']);
        $this->assertArrayHasKey('children', $hierarchy[0]);
        $this->assertCount(1, $hierarchy[0]['children']);
    }

    public function test_toggle_status()
    {
        $department = Department::create([
            'name' => 'Test Department',
            'active' => true
        ]);

        $this->service->toggleStatus($department);
        $department->refresh();

        $this->assertFalse($department->active);

        $this->service->toggleStatus($department);
        $department->refresh();

        $this->assertTrue($department->active);
    }

    public function test_move_department()
    {
        $parent1 = Department::create([
            'name' => 'Parent 1',
            'active' => true
        ]);

        $parent2 = Department::create([
            'name' => 'Parent 2',
            'active' => true
        ]);

        $child = Department::create([
            'name' => 'Child Department',
            'parent_id' => $parent1->id,
            'active' => true
        ]);

        $this->service->moveDepartment($child, $parent2->id);
        $child->refresh();

        $this->assertEquals($parent2->id, $child->parent_id);
    }

    public function test_move_department_to_self_throws_exception()
    {
        $department = Department::create([
            'name' => 'Test Department',
            'active' => true
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot move department to itself');

        $this->service->moveDepartment($department, $department->id);
    }

    public function test_reorder_departments()
    {
        $dept1 = Department::create(['name' => 'Dept 1', 'active' => true, 'order' => 1]);
        $dept2 = Department::create(['name' => 'Dept 2', 'active' => true, 'order' => 2]);
        $dept3 = Department::create(['name' => 'Dept 3', 'active' => true, 'order' => 3]);

        $newOrder = [$dept3->id, $dept1->id, $dept2->id];

        $this->service->reorderDepartments($newOrder);

        $dept1->refresh();
        $dept2->refresh();
        $dept3->refresh();

        $this->assertEquals(2, $dept1->order);
        $this->assertEquals(3, $dept2->order);
        $this->assertEquals(1, $dept3->order);
    }

    public function test_search_departments()
    {
        Department::create(['name' => 'IT Department', 'active' => true]);
        Department::create(['name' => 'HR Department', 'active' => true]);
        Department::create(['name' => 'Finance Department', 'active' => true]);

        $results = $this->service->searchDepartments('IT');

        $this->assertCount(1, $results);
        $this->assertEquals('IT Department', $results->first()->name);
    }

    public function test_get_department_stats()
    {
        $department = Department::create([
            'name' => 'Test Department',
            'manager_id' => $this->user->id,
            'active' => true
        ]);

        // Create child departments
        Department::create([
            'name' => 'Child 1',
            'parent_id' => $department->id,
            'active' => true
        ]);

        Department::create([
            'name' => 'Child 2',
            'parent_id' => $department->id,
            'active' => true
        ]);

        $stats = $this->service->getDepartmentStats($department);

        $this->assertArrayHasKey('children_count', $stats);
        $this->assertArrayHasKey('users_count', $stats);
        $this->assertArrayHasKey('active_children', $stats);
        $this->assertEquals(2, $stats['children_count']);
        $this->assertEquals(2, $stats['active_children']);
    }

    public function test_paginate_with_filters()
    {
        Department::create(['name' => 'Active Dept', 'active' => true]);
        Department::create(['name' => 'Inactive Dept', 'active' => false]);

        $filters = ['active' => true];
        $result = $this->service->paginate(10, $filters);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Active Dept', $result->items()[0]->name);
    }
}
