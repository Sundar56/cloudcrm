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
        Schema::create('phpbb_forums', function (Blueprint $table) {
            $table->mediumIncrements('forum_id');
            $table->unsignedMediumInteger('parent_id')->default(0);
            $table->unsignedMediumInteger('left_id')->default(0);
            $table->unsignedMediumInteger('right_id')->default(0);
            $table->mediumText('forum_parents');
            $table->string('forum_name')->default('');
            $table->text('forum_desc');
            $table->string('forum_desc_bitfield')->default('');
            $table->unsignedInteger('forum_desc_options')->default(7);
            $table->string('forum_desc_uid', 8)->default('');
            $table->string('forum_link')->default('');
            $table->string('forum_password', 40)->default('');
            $table->unsignedMediumInteger('forum_style')->default(0);
            $table->string('forum_image')->default('');
            $table->text('forum_rules');
            $table->string('forum_rules_link')->default('');
            $table->string('forum_rules_bitfield')->default('');
            $table->unsignedInteger('forum_rules_options')->default(7);
            $table->string('forum_rules_uid', 8)->default('');
            $table->tinyInteger('forum_topics_per_page')->default(0);
            $table->tinyInteger('forum_type')->default(0);
            $table->tinyInteger('forum_status')->default(0);
            $table->unsignedMediumInteger('forum_posts')->default(0);
            $table->unsignedMediumInteger('forum_topics')->default(0);
            $table->unsignedMediumInteger('forum_topics_real')->default(0);
            $table->unsignedMediumInteger('forum_last_post_id')->default(0)->index('forum_lastpost_id');
            $table->unsignedMediumInteger('forum_last_poster_id')->default(0);
            $table->string('forum_last_post_subject')->default('');
            $table->unsignedInteger('forum_last_post_time')->default(0);
            $table->string('forum_last_poster_name')->default('');
            $table->string('forum_last_poster_colour', 6)->default('');
            $table->tinyInteger('forum_flags')->default(32);
            $table->unsignedInteger('forum_options')->default(0);
            $table->unsignedTinyInteger('display_subforum_list')->default(1);
            $table->unsignedTinyInteger('display_on_index')->default(1);
            $table->unsignedTinyInteger('enable_indexing')->default(1);
            $table->unsignedTinyInteger('enable_icons')->default(1);
            $table->unsignedTinyInteger('enable_prune')->default(0);
            $table->unsignedInteger('prune_next')->default(0);
            $table->unsignedMediumInteger('prune_days')->default(0);
            $table->unsignedMediumInteger('prune_viewed')->default(0);
            $table->unsignedMediumInteger('prune_freq')->default(0);

            $table->index(['left_id', 'right_id'], 'left_right_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_forums');
    }
};
