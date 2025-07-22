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
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'email')) {
                $table->string('email')->nullable()->after('description');
            }
            if (!Schema::hasColumn('departments', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('departments', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('departments', 'budget')) {
                $table->decimal('budget', 12, 2)->nullable()->after('address');
            }
            if (!Schema::hasColumn('departments', 'color')) {
                $table->string('color', 7)->nullable()->after('budget');
            }
            if (!Schema::hasColumn('departments', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('manager_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'email',
                'phone', 
                'address',
                'budget',
                'color',
                'manager_id',
                'sort_order'
            ]);
        });
    }
};
