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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Event Information
            $table->string('event', 50); // created, updated, deleted, login, logout, etc.
            $table->string('auditable_type'); // Model class name
            $table->unsignedBigInteger('auditable_id'); // Model ID
            
            // User Information
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name')->nullable(); // User name at time of action
            $table->string('user_email')->nullable(); // User email at time of action
            
            // Request Information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            
            // Change Information
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->json('changed_fields')->nullable(); // List of changed fields
            
            // Context Information
            $table->string('module', 50)->nullable(); // Which module triggered this
            $table->string('action', 100)->nullable(); // Specific action performed
            $table->text('description')->nullable(); // Human readable description
            $table->string('severity', 20)->default('info'); // info, warning, critical
            
            // Security Information
            $table->string('session_id')->nullable();
            $table->json('tags')->nullable(); // Additional tags for categorization
            $table->json('metadata')->nullable(); // Additional context data
            
            // Timestamps
            $table->timestamp('occurred_at')->useCurrent(); // When the event actually happened
            $table->timestamps(); // created_at, updated_at
            
            // Indexes for performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'occurred_at']);
            $table->index(['module', 'action']);
            $table->index(['severity', 'occurred_at']);
            $table->index('ip_address');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
