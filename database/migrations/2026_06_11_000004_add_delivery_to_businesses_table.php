<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // null = never requested, pending / approved / rejected
            $table->string('delivery_status')->nullable()->after('owner_id');
            $table->timestamp('delivery_requested_at')->nullable()->after('delivery_status');
            $table->string('delivery_reject_reason')->nullable()->after('delivery_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'delivery_requested_at', 'delivery_reject_reason']);
        });
    }
};
