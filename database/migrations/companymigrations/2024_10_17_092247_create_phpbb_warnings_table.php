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
        Schema::create('phpbb_warnings', function (Blueprint $table) {
            $table->mediumIncrements('warning_id');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedMediumInteger('post_id')->default(0);
            $table->unsignedMediumInteger('log_id')->default(0);
            $table->unsignedInteger('warning_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_warnings');
    }
};
