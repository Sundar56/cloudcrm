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
        Schema::table('cloud_variabler', function (Blueprint $table) {
            if (!Schema::hasColumn('cloud_variabler', 'setting_module_id')) {
                $table->unsignedBigInteger('setting_module_id')->after('id')->nullable();
            }
            if (!Schema::hasColumn('cloud_variabler', 'created_at') && !Schema::hasColumn('cloud_variabler', 'updated_at')) {
                $table->timestamps();
            }
            $table->foreign('setting_module_id')->references('id')->on('tbl_setting_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cloud_variabler', function (Blueprint $table) {
            if (Schema::hasColumn('cloud_variabler', 'setting_id')) {
                $table->dropColumn('setting_id');
            }
            if (Schema::hasColumn('cloud_variabler', 'created_at') && Schema::hasColumn('cloud_variabler', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
