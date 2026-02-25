<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_user_id');  // only logged-in users (is_login = true)
            $table->unsignedBigInteger('question_id');
            $table->date('quiz_date');
            $table->enum('selected_answer', ['A', 'B', 'C', 'D'])->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('time_taken_seconds')->default(0);
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->foreign('app_user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('quiz_questions')->onDelete('cascade');
            $table->unique(['app_user_id', 'quiz_date']); // one attempt per user per day
            $table->index(['app_user_id', 'quiz_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
