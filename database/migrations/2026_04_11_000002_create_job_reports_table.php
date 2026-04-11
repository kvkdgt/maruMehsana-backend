<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_vacancy_id');
            $table->unsignedBigInteger('reported_by');
            $table->enum('reason', ['spam', 'fake_job', 'inappropriate_content', 'other']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->timestamps();

            $table->foreign('job_vacancy_id')->references('id')->on('job_vacancies')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('app_users')->onDelete('cascade');
            $table->unique(['job_vacancy_id', 'reported_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_reports');
    }
};
