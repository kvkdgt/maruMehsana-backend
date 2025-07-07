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
        Schema::table('agency_admins', function (Blueprint $table) {
            // Add soft deletes if not already present
            if (!Schema::hasColumn('agency_admins', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add email_verified_at for Laravel authentication if not present
            if (!Schema::hasColumn('agency_admins', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('username');
            }
            
            // Add remember_token for Laravel authentication if not present
            if (!Schema::hasColumn('agency_admins', 'remember_token')) {
                $table->rememberToken()->after('status');
            }
            
            // Add indexes for better performance
            $table->index(['agency_id', 'status'], 'agency_admins_agency_id_status_index');
            $table->index('email', 'agency_admins_email_index');
            $table->index('username', 'agency_admins_username_index');
            $table->index('status', 'agency_admins_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('agency_admins', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('agency_admins_agency_id_status_index');
            $table->dropIndex('agency_admins_email_index');
            $table->dropIndex('agency_admins_username_index');
            $table->dropIndex('agency_admins_status_index');
            
            // Drop columns
            if (Schema::hasColumn('agency_admins', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
            
            if (Schema::hasColumn('agency_admins', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            
            // Drop soft deletes
            $table->dropSoftDeletes();
        });
    }
};