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
        Schema::table('tbl_setting_options', function (Blueprint $table) {
            $table->foreign('setting_module_id')->references('id')->on('tbl_setting_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_setting_options', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_setting_options', 'setting_module_id')) {
                $table->dropColumn('setting_module_id');
            }
        });
    }
};
