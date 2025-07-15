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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('general')->after('news_article_id'); // general, news, announcement, etc.
            $table->timestamp('auto_scheduled_at')->nullable()->after('scheduled_at'); // For auto-scheduling (like 5 min delay)
            
            // Add foreign key constraint if you want referential integrity
            
            // Add index for better performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['auto_scheduled_at', 'is_sent']);
        });
    }
};