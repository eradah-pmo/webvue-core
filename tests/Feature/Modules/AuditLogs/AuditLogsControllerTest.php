<?php

namespace Tests\Feature\Modules\AuditLogs;

use Tests\TestCase;
use App\Modules\Users\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuditLogsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ]);
    }

    public function test_index_displays_audit_logs()
    {
        // Create test activity log
        activity()
            ->causedBy($this->user)
            ->log('Test activity');

        $response = $this->actingAs($this->user)
            ->get(route('audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AuditLogs/Index')
            ->has('logs.data')
        );
    }

    public function test_show_displays_single_audit_log()
    {
        // Create test activity log
        $activity = activity()
            ->causedBy($this->user)
            ->log('Test activity');

        $response = $this->actingAs($this->user)
            ->get(route('audit-logs.show', $activity->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AuditLogs/Show')
            ->has('log')
        );
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get(route('audit-logs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_authentication()
    {
        $activity = activity()->log('Test activity');
        
        $response = $this->get(route('audit-logs.show', $activity->id));
        $response->assertRedirect(route('login'));
    }

    public function test_index_with_filters()
    {
        // Create multiple activities
        activity()->causedBy($this->user)->log('User activity');
        activity()->log('System activity');

        $response = $this->actingAs($this->user)
            ->get(route('audit-logs.index', ['causer_type' => 'user']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('AuditLogs/Index')
            ->has('logs.data')
        );
    }

    public function test_get_filter_options()
    {
        $response = $this->actingAs($this->user)
            ->get(route('audit-logs.filter-options'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'causer_types',
            'subjects',
            'events'
        ]);
    }
}
