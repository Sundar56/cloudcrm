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
        Schema::create('phpbb_log', function (Blueprint $table) {
            $table->mediumIncrements('log_id');
            $table->tinyInteger('log_type')->default(0)->index('log_type');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->unsignedMediumInteger('forum_id')->default(0)->index('forum_id');
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->unsignedMediumInteger('reportee_id')->default(0)->index('reportee_id');
            $table->string('log_ip', 40)->default('');
            $table->unsignedInteger('log_time')->default(0);
            $table->text('log_operation');
            $table->mediumText('log_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_log');
    }
};
