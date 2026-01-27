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
        Schema::create('tourist_place_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_place_id')->constrained('tourist_places')->onDelete('cascade');
            $table->foreignId('app_user_id')->constrained('app_users')->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Ensure one review per user per place
            $table->unique(['tourist_place_id', 'app_user_id'], 'place_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourist_place_reviews');
    }
};
