<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('app_user_id');   // customer
            $table->unsignedBigInteger('business_id');
            // requested / confirmed / dispatched / delivered / cancelled / rejected
            $table->string('status')->default('requested');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('customer_name')->nullable();   // snapshot
            $table->string('customer_mobile')->nullable(); // snapshot
            $table->string('reject_reason')->nullable();
            $table->timestamps();

            $table->foreign('app_user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->index(['app_user_id', 'status']);
            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
