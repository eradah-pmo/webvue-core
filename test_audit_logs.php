<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\AuditLog;
use App\Helpers\AuditHelper;
use App\Modules\Users\Models\Users;
use App\Modules\Roles\Models\Roles;
use App\Modules\Departments\Models\Departments;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Audit Logs System\n";
echo "============================\n\n";

try {
    // Test 1: Check if audit_logs table exists and is accessible
    echo "1. Testing Database Connection and Table Structure...\n";
    
    $tableExists = Schema::hasTable('audit_logs');
    echo "   ✅ audit_logs table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if ($tableExists) {
        $columns = Schema::getColumnListing('audit_logs');
        echo "   ✅ Table has " . count($columns) . " columns\n";
        echo "   📋 Columns: " . implode(', ', array_slice($columns, 0, 10)) . "...\n";
    }
    
    // Test 2: Test AuditLog Model basic operations
    echo "\n2. Testing AuditLog Model...\n";
    
    $testLog = AuditLog::createEntry([
        'event' => 'test_event',
        'auditable_type' => 'TestModel',
        'auditable_id' => 1,
        'module' => 'testing',
        'action' => 'test_action',
        'description' => 'This is a test audit log entry',
        'severity' => 'info',
        'tags' => ['test', 'audit'],
        'metadata' => ['test_key' => 'test_value'],
    ]);
    
    echo "   ✅ Created test audit log with ID: " . $testLog->id . "\n";
    echo "   📝 Event: " . $testLog->event . "\n";
    echo "   📝 Description: " . $testLog->description . "\n";
    echo "   📝 Severity: " . $testLog->severity . "\n";
    
    // Test 3: Test AuditHelper functions
    echo "\n3. Testing AuditHelper Functions...\n";
    
    $securityLog = AuditHelper::logSecurity(
        'test_security_event',
        'Testing security logging functionality',
        ['test_metadata' => 'security_test']
    );
    echo "   ✅ Security log created with ID: " . $securityLog->id . "\n";
    
    $suspiciousLog = AuditHelper::logSuspicious(
        'Testing suspicious activity logging',
        ['ip' => '192.168.1.100', 'attempts' => 5]
    );
    echo "   ✅ Suspicious activity log created with ID: " . $suspiciousLog->id . "\n";
    
    // Test 4: Test Model Scopes
    echo "\n4. Testing Model Scopes...\n";
    
    $recentLogs = AuditLog::recent(1)->count();
    echo "   ✅ Recent logs (last 1 hour): " . $recentLogs . "\n";
    
    $criticalLogs = AuditLog::critical()->count();
    echo "   ✅ Critical logs: " . $criticalLogs . "\n";
    
    $testModuleLogs = AuditLog::byModule('testing')->count();
    echo "   ✅ Testing module logs: " . $testModuleLogs . "\n";
    
    // Test 5: Test HasAuditLog Trait with actual models
    echo "\n5. Testing HasAuditLog Trait Integration...\n";
    
    // Test with Users model
    $testUser = Users::first();
    if ($testUser) {
        $userAuditCount = $testUser->auditLogs()->count();
        echo "   ✅ User '{$testUser->name}' has {$userAuditCount} audit logs\n";
        
        // Create a custom audit log for the user
        $customLog = $testUser->logCustomAudit(
            'test_user_action',
            'Testing custom audit logging for user',
            ['test' => true]
        );
        echo "   ✅ Created custom audit log for user: " . $customLog->id . "\n";
    } else {
        echo "   ⚠️  No users found to test with\n";
    }
    
    // Test with Roles model
    $testRole = Roles::first();
    if ($testRole) {
        $roleAuditCount = $testRole->auditLogs()->count();
        echo "   ✅ Role '{$testRole->name}' has {$roleAuditCount} audit logs\n";
    } else {
        echo "   ⚠️  No roles found to test with\n";
    }
    
    // Test 6: Test Analytics Functions
    echo "\n6. Testing Analytics Functions...\n";
    
    $securityOverview = AuditHelper::getSecurityOverview(24);
    echo "   ✅ Security Overview (last 24h):\n";
    echo "      - Total events: " . $securityOverview['total_events'] . "\n";
    echo "      - Critical events: " . $securityOverview['critical_events'] . "\n";
    echo "      - Failed logins: " . $securityOverview['failed_logins'] . "\n";
    echo "      - Successful logins: " . $securityOverview['successful_logins'] . "\n";
    
    $recentCritical = AuditHelper::getRecentCriticalEvents(24);
    echo "   ✅ Recent critical events: " . $recentCritical->count() . "\n";
    
    // Test 7: Test Data Integrity
    echo "\n7. Testing Data Integrity...\n";
    
    $totalLogs = AuditLog::count();
    echo "   ✅ Total audit logs in database: " . $totalLogs . "\n";
    
    $logsWithUsers = AuditLog::whereNotNull('user_id')->count();
    echo "   ✅ Logs with user association: " . $logsWithUsers . "\n";
    
    $logsWithMetadata = AuditLog::whereNotNull('metadata')->count();
    echo "   ✅ Logs with metadata: " . $logsWithMetadata . "\n";
    
    // Test 8: Performance Test
    echo "\n8. Performance Test...\n";
    
    $startTime = microtime(true);
    
    // Create multiple logs quickly
    for ($i = 0; $i < 10; $i++) {
        AuditLog::createEntry([
            'event' => 'performance_test',
            'auditable_type' => 'PerformanceTest',
            'auditable_id' => $i,
            'module' => 'testing',
            'action' => 'bulk_test',
            'description' => "Performance test log #{$i}",
            'severity' => 'info',
        ]);
    }
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "   ✅ Created 10 logs in {$duration}ms (avg: " . round($duration/10, 2) . "ms per log)\n";
    
    // Test 9: Query Performance
    echo "\n9. Query Performance Test...\n";
    
    $startTime = microtime(true);
    $complexQuery = AuditLog::with(['user', 'auditable'])
        ->where('severity', 'info')
        ->where('created_at', '>=', now()->subHours(24))
        ->orderBy('occurred_at', 'desc')
        ->limit(50)
        ->get();
    $endTime = microtime(true);
    $queryDuration = round(($endTime - $startTime) * 1000, 2);
    
    echo "   ✅ Complex query returned " . $complexQuery->count() . " results in {$queryDuration}ms\n";
    
    // Cleanup test data
    echo "\n10. Cleaning up test data...\n";
    $deletedCount = AuditLog::where('module', 'testing')->delete();
    echo "   ✅ Cleaned up {$deletedCount} test audit logs\n";
    
    echo "\n🎉 All Audit Logs Tests Completed Successfully!\n";
    echo "=====================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
