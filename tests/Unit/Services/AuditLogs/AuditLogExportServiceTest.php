<?php

namespace Tests\Unit\Services\AuditLogs;

use Tests\TestCase;
use App\Modules\AuditLogs\Services\AuditLogExportService;
use App\Modules\Users\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class AuditLogExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogExportService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditLogExportService();
        
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_export_to_csv()
    {
        // Create test activities
        activity()->causedBy($this->user)->log('Test activity 1');
        activity()->log('Test activity 2');

        $request = new Request();
        $result = $this->service->exportToCsv($request);

        $this->assertIsString($result);
        $this->assertStringContains('ID,Description,Event,Causer,Subject,Created At', $result);
        $this->assertStringContains('Test activity 1', $result);
        $this->assertStringContains('Test activity 2', $result);
    }

    public function test_export_to_excel()
    {
        // Create test activities
        activity()->causedBy($this->user)->log('Excel test activity');

        $request = new Request();
        $result = $this->service->exportToExcel($request);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $result);
        $this->assertStringContains('audit-logs', $result->getFile()->getFilename());
    }

    public function test_export_to_pdf()
    {
        // Create test activities
        activity()->causedBy($this->user)->log('PDF test activity');

        $request = new Request();
        $result = $this->service->exportToPdf($request);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $result);
        $this->assertEquals('application/pdf', $result->headers->get('Content-Type'));
    }

    public function test_export_with_filters()
    {
        // Create activities with different properties
        activity()->causedBy($this->user)->event('created')->log('User created activity');
        activity()->event('updated')->log('System updated activity');

        $request = new Request(['causer_type' => 'user']);
        $result = $this->service->exportToCsv($request);

        $this->assertStringContains('User created activity', $result);
        $this->assertStringNotContains('System updated activity', $result);
    }

    public function test_prepare_export_data()
    {
        // Create test activity with properties
        $activity = activity()
            ->causedBy($this->user)
            ->event('created')
            ->log('Test activity with properties');
        
        $activity->properties = ['key' => 'value'];
        $activity->save();

        $activities = Activity::all();
        $data = $this->service->prepareExportData($activities);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        
        $exportedActivity = $data[0];
        $this->assertArrayHasKey('id', $exportedActivity);
        $this->assertArrayHasKey('description', $exportedActivity);
        $this->assertArrayHasKey('event', $exportedActivity);
        $this->assertArrayHasKey('causer_name', $exportedActivity);
        $this->assertArrayHasKey('created_at', $exportedActivity);
        
        $this->assertEquals('Test activity with properties', $exportedActivity['description']);
        $this->assertEquals('created', $exportedActivity['event']);
        $this->assertEquals($this->user->name, $exportedActivity['causer_name']);
    }

    public function test_export_with_date_range()
    {
        $yesterday = now()->subDay();
        $tomorrow = now()->addDay();

        // Create activities on different dates
        activity()->log('Old activity')->update(['created_at' => $yesterday]);
        activity()->log('New activity');

        $request = new Request([
            'date_from' => now()->format('Y-m-d'),
            'date_to' => $tomorrow->format('Y-m-d')
        ]);

        $result = $this->service->exportToCsv($request);

        $this->assertStringContains('New activity', $result);
        $this->assertStringNotContains('Old activity', $result);
    }

    public function test_export_empty_data()
    {
        $request = new Request();
        $result = $this->service->exportToCsv($request);

        $this->assertStringContains('ID,Description,Event,Causer,Subject,Created At', $result);
        // Should only contain headers
        $lines = explode("\n", trim($result));
        $this->assertCount(1, $lines);
    }

    public function test_export_with_search_filter()
    {
        activity()->log('Important system message');
        activity()->log('Regular activity');

        $request = new Request(['search' => 'Important']);
        $result = $this->service->exportToCsv($request);

        $this->assertStringContains('Important system message', $result);
        $this->assertStringNotContains('Regular activity', $result);
    }

    public function test_get_export_filename()
    {
        $filename = $this->service->getExportFilename('csv');
        
        $this->assertStringStartsWith('audit-logs-', $filename);
        $this->assertStringEndsWith('.csv', $filename);
        $this->assertStringContains(date('Y-m-d'), $filename);
    }
}
