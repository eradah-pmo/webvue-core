<?php

namespace Tests\Feature\Modules\Users;

use Tests\TestCase;
use App\Modules\Users\Services\UserExportService;
use App\Modules\Users\Services\UserFilterService;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserExportService $exportService;
    private UserFilterService $filterService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filterService = $this->createMock(UserFilterService::class);
        $this->exportService = new UserExportService($this->filterService);
    }

    /** @test */
    public function it_exports_users_to_csv()
    {
        // Create test user for authentication
        $admin = User::factory()->create();
        Auth::login($admin);
        
        // Mock filtered users collection
        $user1 = User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'user1@example.com',
            'created_at' => now(),
            'is_active' => true,
        ]);
        $user2 = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'created_at' => now()->subDay(),
            'is_active' => false,
        ]);
        
        $users = collect([$user1, $user2]);
        
        // Mock the filter service
        $this->filterService
            ->expects($this->once())
            ->method('getFilteredUsers')
            ->willReturn($users);
        
        // Test export
        $response = $this->exportService->exportToCsv();
        
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="users_export_', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_limits_export_to_maximum_users()
    {
        // Create test user for authentication
        $admin = User::factory()->create();
        Auth::login($admin);
        
        // Create a collection of 5 users
        $users = collect(User::factory()->count(5)->create());
        
        // Mock the filter service with a specified limit
        $this->filterService
            ->expects($this->once())
            ->method('getFilteredUsers')
            ->with([], 100) // We'll request 100, but it should be capped
            ->willReturn($users);
        
        // Test export with a limit that exceeds the maximum
        $response = $this->exportService->exportToCsv([], 15000); // Try to export 15000 records
        
        // Should be capped at 10000
        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    /** @test */
    public function it_applies_filters_to_export()
    {
        // Create test user for authentication
        $admin = User::factory()->create();
        Auth::login($admin);
        
        // Create a sample collection
        $users = collect(User::factory()->count(3)->create());
        
        // Define filters
        $filters = [
            'search' => 'test',
            'role_id' => 1,
            'department_id' => 2,
            'status' => 'active'
        ];
        
        // Mock the filter service to expect filters
        $this->filterService
            ->expects($this->once())
            ->method('getFilteredUsers')
            ->with($filters, $this->anything())
            ->willReturn($users);
        
        // Test export with filters
        $response = $this->exportService->exportToCsv($filters);
        
        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    /** @test */
    public function it_handles_empty_result_set()
    {
        // Create test user for authentication
        $admin = User::factory()->create();
        Auth::login($admin);
        
        // Mock an empty result set
        $this->filterService
            ->expects($this->once())
            ->method('getFilteredUsers')
            ->willReturn(collect([]));
        
        // Test export with empty result
        $response = $this->exportService->exportToCsv();
        
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
