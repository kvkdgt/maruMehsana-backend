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
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('whatsapp_no')->nullable()->after('visitors');
            $table->string('mobile_no')->nullable()->after('whatsapp_no');
            $table->string('website_url')->nullable()->after('mobile_no');
            $table->string('email_id')->nullable()->after('website_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_no', 'mobile_no', 'website_url', 'email_id']);
        });
    }
};
