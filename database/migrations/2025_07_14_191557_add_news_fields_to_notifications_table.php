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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('news_article_id')->nullable()->after('banner');
            $table->string('type')->default('general')->after('audience'); // general, news, etc.
            
            // Add foreign key constraint
            $table->foreign('news_article_id')->references('id')->on('news_articles')->onDelete('set null');
            
            // Add index for better performance
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['news_article_id']);
            $table->dropIndex(['type', 'created_at']);
            $table->dropColumn(['news_article_id', 'type']);
        });
    }
};
