<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // حجم الملف بالبايت
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module')->nullable(); // اسم الموديول المرتبط بالملف
            $table->unsignedBigInteger('module_id')->nullable(); // معرف العنصر في الموديول
            $table->boolean('is_public')->default(false); // هل الملف عام أم خاص
            $table->timestamps();
            
            // إنشاء فهارس للبحث السريع
            $table->index(['user_id', 'module', 'module_id']);
            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
