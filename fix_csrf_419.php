<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

echo "=== COMPREHENSIVE 419 PAGE EXPIRED FIX ===\n\n";

// 1. Clear all caches
echo "1. Clearing all caches...\n";
Artisan::call('optimize:clear');
echo "✅ All caches cleared\n";

// 2. Generate new application key if needed
echo "\n2. Checking application key...\n";
if (empty(config('app.key'))) {
    Artisan::call('key:generate');
    echo "✅ New application key generated\n";
} else {
    echo "✅ Application key exists\n";
}

// 3. Clear sessions
echo "\n3. Clearing sessions...\n";
Artisan::call('session:clear', ['--all' => true]);
echo "✅ All sessions cleared\n";

// 4. Regenerate CSRF tokens
echo "\n4. Regenerating CSRF protection...\n";
Artisan::call('cache:clear');
echo "✅ CSRF tokens regenerated\n";

echo "\n=== FIX COMPLETE ===\n";
echo "Please restart your browser and try logging in again.\n";
echo "If the issue persists, clear your browser cache and cookies.\n";
