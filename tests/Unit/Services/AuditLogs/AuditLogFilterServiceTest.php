<?php

namespace Tests\Unit\Services\AuditLogs;

use Tests\TestCase;
use App\Modules\AuditLogs\Services\AuditLogFilterService;
use App\Modules\Users\Models\User;
use App\Models\AuditLogSimple;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditLogFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogFilterService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditLogFilterService();
        
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_service_instantiation()
    {
        $this->assertInstanceOf(AuditLogFilterService::class, $this->service);
    }

    public function test_apply_filters_with_empty_filters()
    {
        $query = AuditLogSimple::query();
        $originalSql = $query->toSql();
        
        $this->service->applyFilters($query, []);
        
        // With empty filters, query should remain unchanged
        $this->assertEquals($originalSql, $query->toSql());
    }

    public function test_apply_filters_with_event_filter()
    {
        $query = AuditLogSimple::query();
        $filters = ['event' => 'created'];
        
        $this->service->applyFilters($query, $filters);
        
        $this->assertStringContains('where "event" = ?', $query->toSql());
    }

    public function test_apply_filters_with_user_id_filter()
    {
        $query = AuditLogSimple::query();
        $filters = ['user_id' => 1];
        
        $this->service->applyFilters($query, $filters);
        
        // Should use the byUser scope
        $this->assertStringContains('where "user_id" = ?', $query->toSql());
    }

    public function test_apply_filters_with_date_range()
    {
        $query = AuditLogSimple::query();
        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31'
        ];
        
        $this->service->applyFilters($query, $filters);
        
        $sql = $query->toSql();
        $this->assertStringContains('date("created_at") >= ?', $sql);
        $this->assertStringContains('date("created_at") <= ?', $sql);
    }

    public function test_apply_filters_with_search()
    {
        $query = AuditLogSimple::query();
        $filters = ['search' => 'test'];
        
        $this->service->applyFilters($query, $filters);
        
        $sql = $query->toSql();
        // Should have search conditions for event, auditable_type, and JSON metadata
        $this->assertStringContains('where ("event" like ?', $sql);
    }

    public function test_get_filter_options_structure()
    {
        $options = $this->service->getFilterOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('events', $options);
        $this->assertArrayHasKey('modules', $options);
        $this->assertArrayHasKey('severities', $options);
        $this->assertArrayHasKey('users', $options);
        
        // Test severities are predefined
        $this->assertEquals(['info', 'warning', 'critical'], $options['severities']);
    }

    public function test_create_base_query_returns_builder()
    {
        $query = $this->service->createBaseQuery();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
        
        // Should include user relationship and be ordered by created_at desc
        $sql = $query->toSql();
        $this->assertStringContains('order by "created_at" desc', $sql);
    }
}
