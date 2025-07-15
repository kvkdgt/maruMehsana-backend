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
            $table->unsignedBigInteger('news_article_id')->nullable()->after('audience');
            $table->string('type')->default('general')->after('news_article_id'); // general, news, announcement, etc.
            $table->timestamp('auto_scheduled_at')->nullable()->after('scheduled_at'); // For auto-scheduling (like 5 min delay)
            
            // Add foreign key constraint if you want referential integrity
            $table->foreign('news_article_id')->references('id')->on('news_articles')->onDelete('cascade');
            
            // Add index for better performance
            $table->index(['type', 'is_sent']);
            $table->index(['auto_scheduled_at', 'is_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['news_article_id']);
            $table->dropIndex(['type', 'is_sent']);
            $table->dropIndex(['auto_scheduled_at', 'is_sent']);
            $table->dropColumn(['news_article_id', 'type', 'auto_scheduled_at']);
        });
    }
};