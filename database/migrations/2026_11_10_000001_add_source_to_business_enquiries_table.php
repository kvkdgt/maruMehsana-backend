<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where an enquiry came from. Defaults to "app": every existing row came from
     * the mobile app, and the app (which cannot be updated) keeps calling the API
     * without sending a source. Only the website form stores "web".
     */
    public function up(): void
    {
        Schema::table('business_enquiries', function (Blueprint $table) {
            $table->enum('source', ['app', 'web'])->default('app')->after('whatsapp_no');
        });
    }

    public function down(): void
    {
        Schema::table('business_enquiries', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
