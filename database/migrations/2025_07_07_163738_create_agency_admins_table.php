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
   public function up(): void {
        Schema::create('agency_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('news_agencies')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique(); // Added username
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('agency_admins');
    }
};
