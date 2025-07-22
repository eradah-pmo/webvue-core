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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->string('category')->default('general')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json, file
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable(); // For select/radio options
            $table->boolean('is_public')->default(false); // Can be accessed by frontend
            $table->boolean('is_encrypted')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['category', 'active']);
            $table->index(['is_public', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
