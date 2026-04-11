<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('posted_by');
            $table->string('title');
            $table->string('company_name');
            $table->enum('job_type', ['full_time', 'part_time', 'freelance', 'internship', 'contract']);
            $table->string('location');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->enum('salary_type', ['monthly', 'yearly', 'hourly', 'not_disclosed'])->default('not_disclosed');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->unsignedInteger('vacancies_count')->default(1);
            $table->string('experience_required', 100)->nullable();
            $table->string('education_required')->nullable();
            $table->enum('gender_preference', ['any', 'male', 'female'])->default('any');
            $table->string('contact_name');
            $table->string('contact_mobile', 20);
            $table->string('contact_email')->nullable();
            $table->enum('apply_via', ['whatsapp', 'call', 'email', 'walk_in'])->default('call');
            $table->string('thumbnail')->nullable();
            $table->date('expires_at')->nullable();
            $table->enum('status', ['open', 'filled', 'closed'])->default('open');
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->foreign('posted_by')->references('id')->on('app_users')->onDelete('cascade');
            $table->index(['is_active', 'status']);
            $table->index('posted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
