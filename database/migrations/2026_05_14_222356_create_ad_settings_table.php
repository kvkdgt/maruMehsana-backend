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
        Schema::create('ad_settings', function (Blueprint $table) {
            $table->id();
            $table->string('placement_key')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->string('ad_unit_id_android')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_settings');
    }
};
