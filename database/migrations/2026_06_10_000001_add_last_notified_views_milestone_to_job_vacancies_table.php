<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            // Highest views milestone (10, 30, 50, ...) already pushed to the poster.
            // Prevents sending the same milestone notification more than once.
            $table->unsignedInteger('last_notified_views_milestone')->default(0)->after('views_count');
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropColumn('last_notified_views_milestone');
        });
    }
};
