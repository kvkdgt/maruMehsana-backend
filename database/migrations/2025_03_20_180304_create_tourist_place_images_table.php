<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tourist_place_images', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->foreignId('tourist_place_id')->constrained('tourist_places')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tourist_place_images');
    }
};
