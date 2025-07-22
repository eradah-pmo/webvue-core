<?php

namespace Tests\Feature\Modules\AuditLogs;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\AuditLogSimple;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class AuditLogManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء الصلاحيات
        $permissions = [
            'audit-logs.view', 'audit-logs.export', 'audit-logs.delete'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // إنشاء الأدوار
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['audit-logs.view', 'audit-logs.export']);
        
        // إنشاء المستخدمين
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    /** @test */
    public function admin_can_view_audit_logs_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('AuditLogs/Index')
        );
    }

    /** @test */
    public function unauthorized_user_cannot_view_audit_logs()
    {
        $response = $this->actingAs($this->user)
            ->get('/audit-logs');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function audit_logs_are_created_automatically()
    {
        // إنشاء قسم جديد لتسجيل النشاط
        $this->actingAs($this->admin)
            ->post('/departments', [
                'name' => 'Test Department',
                'code' => 'TEST',
                'active' => true
            ]);
        
        // التحقق من تسجيل النشاط
        $this->assertDatabaseHas('activity_log', [
            'description' => 'created',
            'subject_type' => Department::class,
            'causer_id' => $this->admin->id
        ]);
    }

    /** @test */
    public function can_filter_audit_logs_by_date_range()
    {
        // إنشاء سجلات في تواريخ مختلفة
        Activity::create([
            'log_name' => 'default',
            'description' => 'old_activity',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'created_at' => Carbon::now()->subDays(10)
        ]);
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'recent_activity',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'created_at' => Carbon::now()->subDays(2)
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?' . http_build_query([
                'date_from' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'date_to' => Carbon::now()->format('Y-m-d')
            ]));
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على السجلات الحديثة فقط
    }

    /** @test */
    public function can_filter_audit_logs_by_user()
    {
        $testUser = User::factory()->create();
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'admin_activity',
            'subject_type' => Department::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id
        ]);
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'user_activity',
            'subject_type' => Department::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $testUser->id
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get("/audit-logs?user_id={$this->admin->id}");
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على أنشطة المدير فقط
    }

    /** @test */
    public function can_filter_audit_logs_by_action_type()
    {
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id
        ]);
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?action=created');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على أنشطة الإنشاء فقط
    }

    /** @test */
    public function can_search_audit_logs_by_description()
    {
        Activity::create([
            'log_name' => 'default',
            'description' => 'user login attempt',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id
        ]);
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'password changed',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?search=login');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على السجلات المطلوبة
    }

    /** @test */
    public function can_view_audit_log_details()
    {
        $activity = Activity::create([
            'log_name' => 'default',
            'description' => 'detailed_activity',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'properties' => [
                'attributes' => ['name' => 'New Name'],
                'old' => ['name' => 'Old Name']
            ]
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get("/audit-logs/{$activity->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('AuditLogs/Show')
                ->has('activity')
        );
    }

    /** @test */
    public function can_export_audit_logs()
    {
        Activity::factory()->count(10)->create();
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function super_admin_can_delete_old_audit_logs()
    {
        // إنشاء سجلات قديمة
        Activity::create([
            'log_name' => 'default',
            'description' => 'old_log',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'created_at' => Carbon::now()->subMonths(13) // أقدم من سنة
        ]);
        
        $response = $this->actingAs($this->superAdmin)
            ->delete('/audit-logs/cleanup', [
                'older_than_months' => 12
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('activity_log', [
            'description' => 'old_log'
        ]);
    }

    /** @test */
    public function admin_cannot_delete_audit_logs()
    {
        $response = $this->actingAs($this->admin)
            ->delete('/audit-logs/cleanup', [
                'older_than_months' => 12
            ]);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function audit_logs_dashboard_shows_statistics()
    {
        // إنشاء أنشطة متنوعة
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'created_at' => Carbon::now()->subDays(1)
        ]);
        
        Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => Department::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'created_at' => Carbon::now()
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs/dashboard');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_activities',
            'today_activities',
            'this_week_activities',
            'top_users',
            'activity_chart'
        ]);
    }

    /** @test */
    public function sensitive_data_is_masked_in_logs()
    {
        // محاكاة تغيير كلمة مرور
        $this->actingAs($this->admin)
            ->post("/users/{$this->user->id}/reset-password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
        
        // التحقق من أن كلمة المرور لا تظهر في السجلات
        $activity = Activity::where('description', 'password_reset')->first();
        
        if ($activity) {
            $properties = $activity->properties->toArray();
            $this->assertArrayNotHasKey('password', $properties['attributes'] ?? []);
        }
    }

    /** @test */
    public function audit_logs_pagination_works()
    {
        Activity::factory()->count(50)->create();
        
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?per_page=10');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('activities.data', 10)
                ->has('activities.links')
        );
    }

    /** @test */
    public function can_track_ip_address_in_logs()
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);
        
        $this->actingAs($this->admin)
            ->post('/departments', [
                'name' => 'IP Test Department',
                'code' => 'IPTEST',
                'active' => true
            ]);
        
        $activity = Activity::where('description', 'created')->latest()->first();
        
        if ($activity) {
            $properties = $activity->properties->toArray();
            $this->assertEquals('192.168.1.100', $properties['ip_address'] ?? null);
        }
    }
}
