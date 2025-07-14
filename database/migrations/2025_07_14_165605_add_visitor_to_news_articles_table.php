<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->unsignedBigInteger('visitor')->default(0)->after('is_for_mehsana');
            $table->index('visitor'); // For sorting by popularity
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropIndex(['visitor']);
            $table->dropColumn('visitor');
        });
    }
};