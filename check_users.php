<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== التحقق من المستخدمين الموجودين ===\n\n";

try {
    $users = User::all();
    
    if ($users->count() > 0) {
        echo "👥 المستخدمون الموجودون:\n";
        foreach ($users as $user) {
            echo "- الاسم: {$user->name}\n";
            echo "  البريد: {$user->email}\n";
            echo "  نشط: " . ($user->active ? 'نعم' : 'لا') . "\n";
            echo "  تاريخ الإنشاء: {$user->created_at}\n\n";
        }
    } else {
        echo "❌ لا يوجد مستخدمون في قاعدة البيانات\n";
        echo "🔧 إنشاء مستخدم تجريبي...\n";
        
        $testUser = User::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'active' => true,
        ]);
        
        echo "✅ تم إنشاء مستخدم تجريبي:\n";
        echo "📧 البريد: test@admin.com\n";
        echo "🔐 كلمة المرور: password\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📝 التفاصيل: " . $e->getTraceAsString() . "\n";
}
