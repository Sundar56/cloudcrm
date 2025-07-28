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
        Schema::create('phpbb_topics', function (Blueprint $table) {
            $table->mediumIncrements('topic_id');
            $table->unsignedMediumInteger('forum_id')->default(0)->index('forum_id');
            $table->unsignedMediumInteger('icon_id')->default(0);
            $table->unsignedTinyInteger('topic_attachment')->default(0);
            $table->unsignedTinyInteger('topic_approved')->default(1)->index('topic_approved');
            $table->unsignedTinyInteger('topic_reported')->default(0);
            $table->string('topic_title')->default('');
            $table->unsignedMediumInteger('topic_poster')->default(0);
            $table->unsignedInteger('topic_time')->default(0);
            $table->unsignedInteger('topic_time_limit')->default(0);
            $table->unsignedMediumInteger('topic_views')->default(0);
            $table->unsignedMediumInteger('topic_replies')->default(0);
            $table->unsignedMediumInteger('topic_replies_real')->default(0);
            $table->tinyInteger('topic_status')->default(0);
            $table->tinyInteger('topic_type')->default(0);
            $table->unsignedMediumInteger('topic_first_post_id')->default(0);
            $table->string('topic_first_poster_name')->default('');
            $table->string('topic_first_poster_colour', 6)->default('');
            $table->unsignedMediumInteger('topic_last_post_id')->default(0);
            $table->unsignedMediumInteger('topic_last_poster_id')->default(0);
            $table->string('topic_last_poster_name')->default('');
            $table->string('topic_last_poster_colour', 6)->default('');
            $table->string('topic_last_post_subject')->default('');
            $table->unsignedInteger('topic_last_post_time')->default(0)->index('last_post_time');
            $table->unsignedInteger('topic_last_view_time')->default(0);
            $table->unsignedMediumInteger('topic_moved_id')->default(0);
            $table->unsignedTinyInteger('topic_bumped')->default(0);
            $table->unsignedMediumInteger('topic_bumper')->default(0);
            $table->string('poll_title')->default('');
            $table->unsignedInteger('poll_start')->default(0);
            $table->unsignedInteger('poll_length')->default(0);
            $table->tinyInteger('poll_max_options')->default(1);
            $table->unsignedInteger('poll_last_vote')->default(0);
            $table->unsignedTinyInteger('poll_vote_change')->default(0);

            $table->index(['forum_id', 'topic_last_post_time', 'topic_moved_id'], 'fid_time_moved');
            $table->index(['forum_id', 'topic_approved', 'topic_last_post_id'], 'forum_appr_last');
            $table->index(['forum_id', 'topic_type'], 'forum_id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_topics');
    }
};
