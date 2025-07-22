<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DashboardService;
use App\Modules\Users\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $dashboardService;
    protected User $superAdmin;
    protected User $regularUser;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // تكوين الحماية في Spatie Permission
        Config::set('auth.defaults.guard', 'web');
        Config::set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        Config::set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);

        // إنشاء الخدمة
        $this->dashboardService = app(DashboardService::class);

        // إنشاء الأدوار والصلاحيات مع تحديد guard_name
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);

        // إنشاء الصلاحيات مع تحديد guard_name
        $viewUsers = Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
        $viewDepartments = Permission::create(['name' => 'departments.view', 'guard_name' => 'web']);
        $viewRoles = Permission::create(['name' => 'roles.view', 'guard_name' => 'web']);
        $viewDashboard = Permission::create(['name' => 'dashboard.view', 'guard_name' => 'web']);

        // ربط الصلاحيات بالأدوار
        $superAdminRole->givePermissionTo([
            $viewUsers, $viewDepartments, $viewRoles, $viewDashboard
        ]);
        
        $userRole->givePermissionTo([
            $viewDashboard
        ]);

        // إنشاء قسم
        $this->department = Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'description' => 'Information Technology',
            'active' => true
        ]);

        // إنشاء مستخدم بصلاحيات كاملة
        $this->superAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'active' => true
        ]);
        $this->superAdmin->assignRole('super-admin');

        // إنشاء مستخدم عادي
        $this->regularUser = User::factory()->create([
            'department_id' => $this->department->id,
            'active' => true
        ]);
        $this->regularUser->assignRole('user');

        // إنشاء بيانات إضافية للاختبار
        User::factory()->count(5)->create([
            'department_id' => $this->department->id,
            'active' => true,
            'created_at' => now()->subDays(2)
        ]);

        User::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'active' => false,
            'created_at' => now()->subDays(10)
        ]);

        // إنشاء أنشطة
        for ($i = 0; $i < 5; $i++) {
            activity()
                ->causedBy($this->superAdmin)
                ->log('Test activity ' . $i);
        }
    }

    /** @test */
    public function it_returns_advanced_stats_for_super_admin()
    {
        $stats = $this->dashboardService->getAdvancedStats($this->superAdmin);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('roles', $stats);
        $this->assertArrayHasKey('departments', $stats);
        $this->assertArrayHasKey('activities', $stats);
    }

    /** @test */
    public function it_returns_limited_stats_for_regular_user()
    {
        $stats = $this->dashboardService->getAdvancedStats($this->regularUser);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('activities', $stats);
        
        // يجب ألا تحتوي على إحصائيات المستخدمين لأن المستخدم العادي لا يملك صلاحية عرضها
        $this->assertArrayNotHasKey('users', $stats);
    }

    /** @test */
    public function it_calculates_percentage_change_correctly()
    {
        // استخدام الريفلكشن للوصول إلى الدالة الخاصة
        $reflectionClass = new \ReflectionClass(DashboardService::class);
        $method = $reflectionClass->getMethod('calculatePercentageChange');
        $method->setAccessible(true);

        // اختبار حالة الزيادة
        $result = $method->invoke($this->dashboardService, 100, 50);
        $this->assertEquals('+100.0%', $result['formatted']);
        $this->assertEquals('positive', $result['type']);

        // اختبار حالة النقصان
        $result = $method->invoke($this->dashboardService, 50, 100);
        $this->assertEquals('-50.0%', $result['formatted']);
        $this->assertEquals('negative', $result['type']);

        // اختبار حالة عدم التغيير
        $result = $method->invoke($this->dashboardService, 100, 100);
        $this->assertEquals('0.0%', $result['formatted']);
        $this->assertEquals('neutral', $result['type']);

        // اختبار حالة القيمة السابقة صفر
        $result = $method->invoke($this->dashboardService, 100, 0);
        $this->assertEquals('+100%', $result['formatted']);
        $this->assertEquals('positive', $result['type']);

        // اختبار حالة القيمتين صفر
        $result = $method->invoke($this->dashboardService, 0, 0);
        $this->assertEquals('0%', $result['formatted']);
        $this->assertEquals('neutral', $result['type']);
    }

    /** @test */
    public function it_returns_system_alerts_for_inactive_users()
    {
        $alerts = $this->dashboardService->getSystemAlerts($this->superAdmin);

        $this->assertIsArray($alerts);
        $this->assertNotEmpty($alerts);

        $inactiveUserAlert = collect($alerts)->firstWhere('id', 'inactive_users');
        $this->assertNotNull($inactiveUserAlert);
        $this->assertEquals('warning', $inactiveUserAlert['type']);
        $this->assertStringContainsString('3', $inactiveUserAlert['message']); // يجب أن تحتوي الرسالة على عدد المستخدمين غير النشطين
    }

    /** @test */
    public function it_returns_users_chart_data()
    {
        // استخدام الريفلكشن للوصول إلى الدالة الخاصة
        $reflectionClass = new \ReflectionClass(DashboardService::class);
        $method = $reflectionClass->getMethod('getUsersChartData');
        $method->setAccessible(true);

        $chartData = $method->invoke($this->dashboardService, $this->superAdmin);

        $this->assertIsArray($chartData);
        $this->assertCount(7, $chartData); // يجب أن تحتوي على بيانات 7 أيام
        $this->assertIsNumeric($chartData[6]); // اليوم الحالي
    }

    /** @test */
    public function it_returns_activities_chart_data()
    {
        // استخدام الريفلكشن للوصول إلى الدالة الخاصة
        $reflectionClass = new \ReflectionClass(DashboardService::class);
        $method = $reflectionClass->getMethod('getActivitiesChartData');
        $method->setAccessible(true);

        $chartData = $method->invoke($this->dashboardService, $this->superAdmin);

        $this->assertIsArray($chartData);
        $this->assertCount(7, $chartData); // يجب أن تحتوي على بيانات 7 أيام
    }

    /** @test */
    public function it_caches_dashboard_stats()
    {
        // تنظيف الكاش أولاً
        Cache::flush();

        // الاستدعاء الأول - يجب أن يحسب ويخزن في الكاش
        $stats1 = $this->dashboardService->getAdvancedStats($this->superAdmin);

        // التحقق من وجود البيانات في الكاش
        $cacheKey = "dashboard_stats_{$this->superAdmin->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // إنشاء مستخدمين جدد لن يظهروا في النتائج المخزنة مؤقتًا
        User::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'active' => true
        ]);

        // الاستدعاء الثاني - يجب أن يأتي من الكاش
        $stats2 = $this->dashboardService->getAdvancedStats($this->superAdmin);

        // يجب أن تكون النتائج متطابقة رغم إضافة مستخدمين جدد
        $this->assertEquals($stats1, $stats2);
    }

    /** @test */
    public function it_respects_user_permissions_for_users_stats()
    {
        // إنشاء مستخدم بصلاحية عرض المستخدمين فقط
        $user = User::factory()->create([
            'department_id' => $this->department->id
        ]);
        $user->givePermissionTo('users.view');
        $user->givePermissionTo('dashboard.view');

        $stats = $this->dashboardService->getAdvancedStats($user);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayNotHasKey('departments', $stats);
        $this->assertArrayNotHasKey('roles', $stats);
    }

    /** @test */
    public function it_filters_activities_by_accessible_users()
    {
        // إنشاء قسم آخر
        $otherDepartment = Department::create([
            'name' => 'HR Department',
            'code' => 'HR',
            'description' => 'Human Resources',
            'active' => true
        ]);

        // إنشاء مستخدم في القسم الآخر
        $otherUser = User::factory()->create([
            'department_id' => $otherDepartment->id
        ]);

        // إنشاء نشاط للمستخدم الآخر
        activity()
            ->causedBy($otherUser)
            ->log('Activity from other department');

        // استخدام الريفلكشن للوصول إلى الدالة الخاصة
        $reflectionClass = new \ReflectionClass(DashboardService::class);
        $method = $reflectionClass->getMethod('getActivitiesStats');
        $method->setAccessible(true);

        // اختبار أن المستخدم العادي لا يرى أنشطة المستخدمين من أقسام أخرى
        $regularUserStats = $method->invoke($this->dashboardService, $this->regularUser);
        
        // اختبار أن المشرف يرى جميع الأنشطة
        $superAdminStats = $method->invoke($this->dashboardService, $this->superAdmin);

        $this->assertGreaterThan($regularUserStats['current'], $superAdminStats['current']);
    }
}
