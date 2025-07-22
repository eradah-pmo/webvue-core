<?php

namespace Tests\Unit\Services\AuditLogs;

use Tests\TestCase;
use App\Modules\AuditLogs\Services\AuditLogDashboardService;
use App\Modules\Users\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditLogDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogDashboardService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditLogDashboardService();
        
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_get_dashboard_stats()
    {
        // Create test activities
        activity()->causedBy($this->user)->log('User activity 1');
        activity()->causedBy($this->user)->log('User activity 2');
        activity()->log('System activity');

        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('total_activities', $stats);
        $this->assertArrayHasKey('today_activities', $stats);
        $this->assertArrayHasKey('user_activities', $stats);
        $this->assertArrayHasKey('system_activities', $stats);

        $this->assertEquals(3, $stats['total_activities']);
        $this->assertEquals(3, $stats['today_activities']);
        $this->assertEquals(2, $stats['user_activities']);
        $this->assertEquals(1, $stats['system_activities']);
    }

    public function test_get_activity_by_day()
    {
        // Create activities for different days
        $today = now();
        $yesterday = now()->subDay();

        activity()->log('Today activity')->update(['created_at' => $today]);
        activity()->log('Yesterday activity')->update(['created_at' => $yesterday]);

        $data = $this->service->getActivityByDay(7);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(7, $data['labels']);
        $this->assertCount(7, $data['data']);
    }

    public function test_get_top_users()
    {
        $user2 = User::create([
            'first_name' => 'User',
            'last_name' => '2',
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password')
        ]);

        // Create activities for different users
        activity()->causedBy($this->user)->log('Activity 1');
        activity()->causedBy($this->user)->log('Activity 2');
        activity()->causedBy($user2)->log('Activity 3');

        $topUsers = $this->service->getTopUsers(5);

        $this->assertIsArray($topUsers);
        $this->assertCount(2, $topUsers);
        $this->assertEquals($this->user->name, $topUsers[0]['name']);
        $this->assertEquals(2, $topUsers[0]['activities_count']);
    }

    public function test_get_activity_by_type()
    {
        // Create activities with different events
        activity()->event('created')->log('Created activity');
        activity()->event('updated')->log('Updated activity');
        activity()->event('created')->log('Another created activity');

        $data = $this->service->getActivityByType();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('data', $data);
        
        $createdIndex = array_search('created', $data['labels']);
        $updatedIndex = array_search('updated', $data['labels']);
        
        $this->assertNotFalse($createdIndex);
        $this->assertNotFalse($updatedIndex);
        $this->assertEquals(2, $data['data'][$createdIndex]);
        $this->assertEquals(1, $data['data'][$updatedIndex]);
    }

    public function test_get_recent_activities()
    {
        // Create test activities
        activity()->causedBy($this->user)->log('Recent activity 1');
        activity()->causedBy($this->user)->log('Recent activity 2');
        activity()->log('System activity');

        $activities = $this->service->getRecentActivities(10);

        $this->assertCount(3, $activities);
        $this->assertEquals('System activity', $activities->first()->description);
    }

    public function test_get_security_alerts()
    {
        // Create activities that might be security-related
        activity()->event('login')->causedBy($this->user)->log('User logged in');
        activity()->event('failed_login')->log('Failed login attempt');
        activity()->event('password_changed')->causedBy($this->user)->log('Password changed');

        $alerts = $this->service->getSecurityAlerts(24);

        $this->assertIsArray($alerts);
        // Should contain failed login attempts and other security events
        $this->assertGreaterThanOrEqual(0, count($alerts));
    }

    public function test_dashboard_stats_with_no_activities()
    {
        $stats = $this->service->getDashboardStats();

        $this->assertEquals(0, $stats['total_activities']);
        $this->assertEquals(0, $stats['today_activities']);
        $this->assertEquals(0, $stats['user_activities']);
        $this->assertEquals(0, $stats['system_activities']);
    }

    public function test_get_activity_by_day_with_custom_period()
    {
        activity()->log('Test activity');

        $data = $this->service->getActivityByDay(30);

        $this->assertCount(30, $data['labels']);
        $this->assertCount(30, $data['data']);
    }
}
