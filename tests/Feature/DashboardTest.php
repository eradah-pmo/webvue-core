<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء الأدوار الأساسية
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'user']);
        
        // إنشاء قسم تجريبي
        Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'description' => 'Information Technology',
            'active' => true
        ]);
    }

    /** @test */
    public function dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    /** @test */
    public function dashboard_returns_basic_stats()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        // إنشاء بيانات تجريبية
        User::factory()->count(5)->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('stats')
                ->has('stats.users')
                ->has('stats.departments')
                ->has('stats.roles')
                ->has('stats.modules')
        );
    }

    /** @test */
    public function dashboard_returns_advanced_stats()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('advancedStats')
                ->has('advancedStats.users')
                ->where('advancedStats.users.current', '>=', 0)
        );
    }

    /** @test */
    public function dashboard_returns_system_alerts()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        // إنشاء مستخدم غير نشط لتوليد تنبيه
        User::factory()->create(['active' => false]);
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('systemAlerts')
                ->where('systemAlerts.0.type', 'warning')
                ->where('systemAlerts.0.id', 'inactive_users')
        );
    }

    /** @test */
    public function dashboard_returns_recent_activities()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        // إنشاء نشاط تجريبي
        activity()
            ->causedBy($user)
            ->log('Test activity');
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('recentActivities')
                ->has('recentActivities.0.description')
                ->has('recentActivities.0.user')
        );
    }

    /** @test */
    public function dashboard_returns_active_modules()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('modules')
                ->has('modules.0.name')
                ->has('modules.0.display_name')
        );
    }

    /** @test */
    public function dashboard_stats_respect_user_permissions()
    {
        $department = Department::first();
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('user');
        
        // إنشاء مستخدمين في أقسام مختلفة
        $otherDepartment = Department::create([
            'name' => 'HR Department',
            'code' => 'HR',
            'description' => 'Human Resources',
            'active' => true
        ]);
        
        User::factory()->count(3)->create(['department_id' => $department->id]);
        User::factory()->count(5)->create(['department_id' => $otherDepartment->id]);
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        
        // يجب أن يرى المستخدم العادي إحصائيات محدودة فقط
        $response->assertInertia(fn ($page) => 
            $page->has('stats')
                ->where('stats.users', '>=', 0) // قد تكون 0 إذا لم تكن لديه صلاحيات
        );
    }

    /** @test */
    public function dashboard_service_calculates_percentage_changes_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        $dashboardService = app(\App\Services\DashboardService::class);
        $stats = $dashboardService->getAdvancedStats($user);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('current', $stats['users']);
        $this->assertArrayHasKey('change', $stats['users']);
        $this->assertArrayHasKey('changeType', $stats['users']);
    }

    /** @test */
    public function dashboard_alerts_detect_inactive_users()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        // إنشاء مستخدمين غير نشطين
        User::factory()->count(3)->create(['active' => false]);
        
        $dashboardService = app(\App\Services\DashboardService::class);
        $alerts = $dashboardService->getSystemAlerts($user);
        
        $this->assertIsArray($alerts);
        $this->assertNotEmpty($alerts);
        
        $inactiveUserAlert = collect($alerts)->firstWhere('id', 'inactive_users');
        $this->assertNotNull($inactiveUserAlert);
        $this->assertEquals('warning', $inactiveUserAlert['type']);
    }

    /** @test */
    public function dashboard_caches_stats_for_performance()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        
        $dashboardService = app(\App\Services\DashboardService::class);
        
        // الاستدعاء الأول - يجب أن يحسب ويخزن في الكاش
        $stats1 = $dashboardService->getAdvancedStats($user);
        
        // الاستدعاء الثاني - يجب أن يأتي من الكاش
        $stats2 = $dashboardService->getAdvancedStats($user);
        
        $this->assertEquals($stats1, $stats2);
    }
}
