<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

try {
    // إنشاء المستخدم
    $user = User::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'active' => true,
    ]);

    echo "✅ تم إنشاء المستخدم بنجاح!\n";
    echo "📧 البريد الإلكتروني: admin@test.com\n";
    echo "🔐 كلمة المرور: password\n";

    // إنشاء دور المدير إذا لم يكن موجوداً
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    
    // تعيين الدور للمستخدم
    $user->assignRole('admin');
    
    echo "🔑 تم تعيين دور المدير للمستخدم\n";
    
    // عرض جميع المستخدمين الموجودين
    echo "\n👥 المستخدمون الموجودون:\n";
    $users = User::all(['name', 'email']);
    foreach ($users as $u) {
        echo "- {$u->name} ({$u->email})\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
