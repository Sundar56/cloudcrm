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
        Schema::create('phpbb_drafts', function (Blueprint $table) {
            $table->mediumIncrements('draft_id');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedMediumInteger('topic_id')->default(0);
            $table->unsignedMediumInteger('forum_id')->default(0);
            $table->unsignedInteger('save_time')->default(0)->index('save_time');
            $table->string('draft_subject')->default('');
            $table->mediumText('draft_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_drafts');
    }
};
