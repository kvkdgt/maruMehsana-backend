<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_user_id'); // only logged-in users (is_login = true)
            $table->string('query', 191);
            $table->timestamps();

            $table->foreign('app_user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->unique(['app_user_id', 'query']); // one row per user per distinct query
            $table->index(['app_user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
