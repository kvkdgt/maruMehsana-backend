<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_saves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_vacancy_id');
            $table->unsignedBigInteger('app_user_id');
            $table->timestamps();

            $table->foreign('job_vacancy_id')->references('id')->on('job_vacancies')->onDelete('cascade');
            $table->foreign('app_user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->unique(['job_vacancy_id', 'app_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_saves');
    }
};
