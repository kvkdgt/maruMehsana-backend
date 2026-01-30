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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the 'general' column that shouldn't exist
            // This column was likely created by mistake and has no default value
            if (Schema::hasColumn('notifications', 'general')) {
                $table->dropColumn('general');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Re-add the column if rolling back (though this shouldn't be needed)
            $table->bigInteger('general')->unsigned()->nullable();
        });
    }
};
