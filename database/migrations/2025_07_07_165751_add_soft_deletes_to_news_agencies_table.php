<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('news_agencies', function (Blueprint $table) {
            // Add soft deletes if not already present
            if (!Schema::hasColumn('news_agencies', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add indexes for better performance
            $table->index(['status', 'created_at'], 'news_agencies_status_created_at_index');
            $table->index('email', 'news_agencies_email_index');
            $table->index('username', 'news_agencies_username_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('news_agencies', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('news_agencies_status_created_at_index');
            $table->dropIndex('news_agencies_email_index');
            $table->dropIndex('news_agencies_username_index');
            
            // Drop soft deletes
            $table->dropSoftDeletes();
        });
    }
};