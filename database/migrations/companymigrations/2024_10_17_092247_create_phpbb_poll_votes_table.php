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
        Schema::create('phpbb_poll_votes', function (Blueprint $table) {
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->tinyInteger('poll_option_id')->default(0);
            $table->unsignedMediumInteger('vote_user_id')->default(0)->index('vote_user_id');
            $table->string('vote_user_ip', 40)->default('')->index('vote_user_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_poll_votes');
    }
};
