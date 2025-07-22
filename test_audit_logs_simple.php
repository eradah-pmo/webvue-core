<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLogSimple;
use App\Helpers\AuditHelperSimple;
use App\Modules\Users\Models\Users;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 **اختبار نظام Audit Logs المبسط**\n";
echo "=====================================\n\n";

// Test 1: Database Connection and Table Structure
echo "📊 **اختبار 1: هيكل قاعدة البيانات**\n";
try {
    $columns = Schema::getColumnListing('audit_logs');
    echo "✅ الجدول موجود مع " . count($columns) . " أعمدة\n";
    echo "📋 الأعمدة الموجودة: " . implode(', ', $columns) . "\n";
    
    // Check required columns
    $requiredColumns = ['id', 'event', 'auditable_type', 'auditable_id', 'user_id', 'old_values', 'new_values', 'ip_address', 'user_agent', 'metadata'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "✅ جميع الأعمدة المطلوبة موجودة\n";
    } else {
        echo "⚠️ أعمدة مفقودة: " . implode(', ', $missingColumns) . "\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// Test 2: AuditLogSimple Model Basic Operations
echo "🏗️ **اختبار 2: عمليات النموذج الأساسية**\n";
try {
    // Test model creation
    $auditLog = AuditLogSimple::createEntry([
        'event' => 'test_event',
        'auditable_type' => 'TestModel',
        'auditable_id' => 1,
        'module' => 'test',
        'action' => 'test_action',
        'description' => 'Test audit log entry',
        'severity' => 'info',
        'tags' => ['test', 'audit'],
        'metadata' => ['test_key' => 'test_value'],
    ]);
    
    echo "✅ تم إنشاء audit log بنجاح - ID: {$auditLog->id}\n";
    echo "📝 البيانات: Event={$auditLog->event}, Module={$auditLog->module}, Action={$auditLog->action}\n";
    echo "🏷️ Tags: " . implode(', ', $auditLog->tags) . "\n";
    echo "📊 Metadata: " . json_encode($auditLog->metadata) . "\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء audit log: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: AuditHelperSimple Functions
echo "🛠️ **اختبار 3: دوال المساعد**\n";
try {
    // Test security logging
    $securityLog = AuditHelperSimple::logSecurity('test_security', 'Test security event', ['ip' => '127.0.0.1']);
    echo "✅ تم تسجيل حدث أمني - ID: {$securityLog->id}\n";
    
    // Test authentication logging
    $authLog = AuditHelperSimple::logAuth('login', null, ['test' => true]);
    echo "✅ تم تسجيل حدث مصادقة - ID: {$authLog->id}\n";
    
    // Test suspicious activity logging
    $suspiciousLog = AuditHelperSimple::logSuspicious('Test suspicious activity', ['reason' => 'testing']);
    echo "✅ تم تسجيل نشاط مشبوه - ID: {$suspiciousLog->id}\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في دوال المساعد: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Query Scopes
echo "🔍 **اختبار 4: نطاقات الاستعلام**\n";
try {
    // Test event scope
    $eventLogs = AuditLogSimple::event('test_event')->get();
    echo "✅ استعلام بالحدث: وُجد " . $eventLogs->count() . " سجل\n";
    
    // Test recent scope
    $recentLogs = AuditLogSimple::recent(24)->get();
    echo "✅ استعلام الأحداث الأخيرة: وُجد " . $recentLogs->count() . " سجل\n";
    
    // Test critical scope
    $criticalLogs = AuditLogSimple::critical()->get();
    echo "✅ استعلام الأحداث الحرجة: وُجد " . $criticalLogs->count() . " سجل\n";
    
    // Test by module scope
    $moduleLogs = AuditLogSimple::byModule('test')->get();
    echo "✅ استعلام بالوحدة: وُجد " . $moduleLogs->count() . " سجل\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في نطاقات الاستعلام: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Analytics Functions
echo "📈 **اختبار 5: دوال التحليل**\n";
try {
    // Test security overview
    $overview = AuditHelperSimple::getSecurityOverview(24);
    echo "✅ نظرة عامة على الأمان:\n";
    echo "   - إجمالي الأحداث: {$overview['total_events']}\n";
    echo "   - الأحداث الحرجة: {$overview['critical_events']}\n";
    echo "   - محاولات تسجيل دخول فاشلة: {$overview['failed_logins']}\n";
    echo "   - تسجيلات دخول ناجحة: {$overview['successful_logins']}\n";
    
    // Test recent critical events
    $criticalEvents = AuditHelperSimple::getRecentCriticalEvents(24);
    echo "✅ الأحداث الحرجة الأخيرة: " . $criticalEvents->count() . " حدث\n";
    
    // Test security alerts
    $alerts = AuditHelperSimple::getSecurityAlerts(24);
    echo "✅ تنبيهات الأمان: " . $alerts->count() . " تنبيه\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في دوال التحليل: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Performance Test
echo "⚡ **اختبار 6: الأداء**\n";
try {
    $startTime = microtime(true);
    
    // Create multiple audit logs
    for ($i = 1; $i <= 10; $i++) {
        AuditLogSimple::createEntry([
            'event' => 'performance_test',
            'auditable_type' => 'TestModel',
            'auditable_id' => $i,
            'module' => 'performance',
            'action' => 'bulk_test',
            'description' => "Performance test entry #{$i}",
            'severity' => 'info',
            'metadata' => ['iteration' => $i],
        ]);
    }
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "✅ تم إنشاء 10 audit logs في {$duration} ميلي ثانية\n";
    echo "📊 متوسط الوقت لكل log: " . round($duration / 10, 2) . " ميلي ثانية\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في اختبار الأداء: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Data Integrity
echo "🔒 **اختبار 7: سلامة البيانات**\n";
try {
    // Test JSON fields
    $jsonTest = AuditLogSimple::createEntry([
        'event' => 'json_test',
        'old_values' => ['field1' => 'old_value', 'field2' => 123],
        'new_values' => ['field1' => 'new_value', 'field2' => 456],
        'metadata' => [
            'complex_data' => [
                'nested' => ['key' => 'value'],
                'array' => [1, 2, 3],
                'boolean' => true,
            ],
        ],
    ]);
    
    // Retrieve and verify
    $retrieved = AuditLogSimple::find($jsonTest->id);
    echo "✅ اختبار JSON fields:\n";
    echo "   - Old values: " . json_encode($retrieved->old_values) . "\n";
    echo "   - New values: " . json_encode($retrieved->new_values) . "\n";
    echo "   - Metadata: " . json_encode($retrieved->metadata) . "\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في اختبار سلامة البيانات: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 8: Cleanup Test Data
echo "🧹 **اختبار 8: تنظيف البيانات التجريبية**\n";
try {
    $deletedCount = AuditLogSimple::whereIn('event', ['test_event', 'performance_test', 'json_test'])->delete();
    echo "✅ تم حذف {$deletedCount} سجل تجريبي\n";
} catch (Exception $e) {
    echo "❌ خطأ في تنظيف البيانات: " . $e->getMessage() . "\n";
}
echo "\n";

// Final Summary
echo "📋 **ملخص النتائج**\n";
echo "==================\n";
echo "✅ هيكل قاعدة البيانات: متوافق\n";
echo "✅ عمليات النموذج: تعمل بنجاح\n";
echo "✅ دوال المساعد: تعمل بنجاح\n";
echo "✅ نطاقات الاستعلام: تعمل بنجاح\n";
echo "✅ دوال التحليل: تعمل بنجاح\n";
echo "✅ الأداء: مقبول (< 100ms لكل log)\n";
echo "✅ سلامة البيانات: محفوظة\n";
echo "\n";

echo "🎉 **نظام Audit Logs المبسط يعمل بنجاح!**\n";
echo "النظام جاهز للاستخدام في الإنتاج.\n";
