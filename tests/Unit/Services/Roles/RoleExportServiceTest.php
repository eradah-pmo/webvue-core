<?php

namespace Tests\Unit\Services\Roles;

use Tests\TestCase;
use App\Modules\Roles\Models\Roles;
use App\Modules\Roles\Services\RoleExportService;
use App\Modules\Roles\Services\RoleFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use Illuminate\Database\Eloquent\Collection;

class RoleExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleExportService $exportService;
    protected $mockFilterService;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create mock filter service
        $this->mockFilterService = Mockery::mock(RoleFilterService::class);
        
        // Inject mock into export service
        $this->exportService = new RoleExportService($this->mockFilterService);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test generating CSV export
     */
    public function test_generate_csv_export()
    {
        // Create test roles collection
        $roles = $this->createTestRolesCollection();
        
        // Setup mock filter service expectation
        $this->mockFilterService
            ->shouldReceive('getFilteredRoles')
            ->once()
            ->with([])
            ->andReturn($roles);
        
        // Mock Auth::user() for audit logging
        $user = Mockery::mock();
        $user->shouldReceive('getAttribute')->andReturn(1);
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($user);
        
        // Generate CSV export
        $response = $this->exportService->exportToCsv([]);
        
        // Verify response is StreamedResponse
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('roles_export_', $response->headers->get('Content-Disposition'));
    }
    
    /**
     * Test generating PDF export (currently returns 501 not implemented)
     */
    public function test_generate_pdf_export()
    {
        // Generate PDF export
        $response = $this->exportService->exportToPdf(['search' => 'test']);
        
        // Verify response is 501 Not Implemented since PDF is not implemented yet
        $this->assertEquals(501, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('PDF export is not implemented yet', $content['error']);
    }
    

    
    /**
     * Helper method to create test roles collection
     * Using mock objects to avoid database relationship complexities
     */
    private function createTestRolesCollection(): Collection
    {
        // Create mock role objects with all required properties
        $adminRole = $this->createMockRole([
            'id' => 1,
            'name' => 'Admin Role',
            'description' => 'Administrator role with full access',
            'active' => true,
            'level' => 1,
            'color' => '#FF0000'
        ]);
        
        $userRole = $this->createMockRole([
            'id' => 2,
            'name' => 'User Role',
            'description' => 'Standard user role with limited access',
            'active' => false,
            'level' => 2,
            'color' => '#0000FF'
        ]);
        
        return new Collection([$adminRole, $userRole]);
    }
    
    /**
     * Helper method to create a mock role with all required properties
     *
     * @param array $attributes
     * @return Roles
     */
    private function createMockRole(array $attributes = []): Roles
    {
        $role = Mockery::mock(Roles::class);
        
        // Set all basic properties
        $role->id = $attributes['id'] ?? 1;
        $role->name = $attributes['name'] ?? 'Test Role';
        $role->guard_name = 'web';
        $role->description = $attributes['description'] ?? 'Test description';
        $role->active = $attributes['active'] ?? true;
        $role->level = $attributes['level'] ?? 1;
        $role->color = $attributes['color'] ?? '#000000';
        $role->created_at = now();
        $role->users_count = 0;
        
        // Mock permissions relationship
        $permissions = collect([]);
        $role->permissions = $permissions;
        
        // Mock users relationship
        $role->shouldReceive('users')->andReturnSelf();
        $role->shouldReceive('count')->andReturn(0);
        
        return $role;
    }
}
