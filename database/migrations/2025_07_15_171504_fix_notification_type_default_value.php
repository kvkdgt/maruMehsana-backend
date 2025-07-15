<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing NULL values to 'general'
        DB::table('notifications')
            ->whereNull('type')
            ->update(['type' => 'general']);
            
        // Then modify the column to have a default value
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->default('general')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });
    }
};