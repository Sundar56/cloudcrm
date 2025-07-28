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
        Schema::table('cloud_sso_users', function (Blueprint $table) {
            if (!Schema::hasColumn('cloud_sso_users', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('cloud_sso_users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('cloud_sso_users', 'phone_work')) {
                $table->string('phone_work')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('cloud_sso_users', 'phone_private')) {
                $table->string('phone_private')->nullable()->after('phone_work');
            }
            if (!Schema::hasColumn('cloud_sso_users', 'title')) {
                $table->string('title')->nullable()->after('phone_private');
            }
            if (!Schema::hasColumn('cloud_sso_users', 'user_image')) {
                $table->string('user_image')->nullable()->after('title');
            }      
            if (!Schema::hasColumn('cloud_sso_users', 'status')) {
                $table->string('status')->nullable()->after('user_image');
            }   
            if (!Schema::hasColumn('cloud_sso_users', 'mfa')) {
                $table->tinyInteger('mfa')->default(0)->after('status');
            }  
            if (!Schema::hasColumn('cloud_sso_users', 'created_at') && !Schema::hasColumn('cloud_sso_users', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cloud_sso_users', function (Blueprint $table) {
            if (Schema::hasColumn('cloud_sso_users', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('cloud_sso_users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('cloud_sso_users', 'phone_work')) {
                $table->dropColumn('phone_work');
            }
            if (Schema::hasColumn('cloud_sso_users', 'phone_private')) {
                $table->dropColumn('phone_private');
            }
            if (Schema::hasColumn('cloud_sso_users', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('cloud_sso_users', 'user_image')) {
                $table->dropColumn('user_image');
            }
            if (Schema::hasColumn('cloud_sso_users', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('cloud_sso_users', 'mfa')) {
                $table->dropColumn('mfa');
            }        
            if (Schema::hasColumn('cloud_sso_users', 'created_at') && Schema::hasColumn('cloud_sso_users', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
