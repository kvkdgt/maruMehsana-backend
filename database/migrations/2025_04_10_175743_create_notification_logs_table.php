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
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->foreignId('app_user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('sent'); // sent, delivered, failed
            $table->text('error_message')->nullable();
            $table->string('device_type')->nullable(); // android, ios, web
            $table->string('fcm_message_id')->nullable(); // To track notification status updates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
