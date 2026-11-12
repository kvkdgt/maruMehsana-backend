<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            // Website views, kept apart from the app's views_count (which drives the poster's milestone notifications).
            $table->unsignedInteger('web_visitors')->default(0)->after('views_count');
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropColumn('web_visitors');
        });
    }
};
