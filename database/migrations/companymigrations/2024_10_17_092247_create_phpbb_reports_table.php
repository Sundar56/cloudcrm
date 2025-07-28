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
        Schema::create('phpbb_reports', function (Blueprint $table) {
            $table->mediumIncrements('report_id');
            $table->unsignedSmallInteger('reason_id')->default(0);
            $table->unsignedMediumInteger('post_id')->default(0)->index('post_id');
            $table->unsignedMediumInteger('pm_id')->default(0)->index('pm_id');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedTinyInteger('user_notify')->default(0);
            $table->unsignedTinyInteger('report_closed')->default(0);
            $table->unsignedInteger('report_time')->default(0);
            $table->mediumText('report_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_reports');
    }
};
