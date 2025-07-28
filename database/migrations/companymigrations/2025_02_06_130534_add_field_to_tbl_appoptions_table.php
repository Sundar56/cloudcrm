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
        Schema::table('tbl_appoptions', function (Blueprint $table) {
            $table->foreign('appmodule_id')->references('id')->on('tbl_appmodules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_appoptions', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_appoptions', 'appmodule_id')) {
                $table->dropColumn('appmodule_id');
            }
        });
    }
};
