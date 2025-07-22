<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Users\Models\Users;

echo "🧪 Testing Users Module...\n";

// Test 1: Create test user
echo "1. Creating test user...\n";
try {
    $user = new Users();
    $user->first_name = 'أحمد';
    $user->last_name = 'محمد';
    $user->name = 'أحمد محمد';
    $user->email = 'ahmed@example.com';
    $user->password = bcrypt('password123');
    $user->phone = '+966501234567';
    $user->locale = 'ar';
    $user->active = true;
    $user->save();
    
    echo "✅ Test user created with ID: {$user->id}\n";
} catch (Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "\n";
}

// Test 2: Count users
echo "2. Counting users...\n";
try {
    $count = Users::count();
    echo "✅ Total users: {$count}\n";
} catch (Exception $e) {
    echo "❌ Error counting users: " . $e->getMessage() . "\n";
}

// Test 3: Test model methods
echo "3. Testing model methods...\n";
try {
    $user = Users::first();
    if ($user) {
        echo "✅ User found: {$user->name} ({$user->email})\n";
        echo "✅ User is " . ($user->active ? 'active' : 'inactive') . "\n";
        echo "✅ User locale: {$user->locale}\n";
    } else {
        echo "❌ No users found\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing model: " . $e->getMessage() . "\n";
}

echo "🎉 Users module test completed!\n";
