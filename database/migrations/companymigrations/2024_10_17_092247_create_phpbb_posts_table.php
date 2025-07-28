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
        Schema::create('phpbb_posts', function (Blueprint $table) {
            $table->mediumIncrements('post_id');
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->unsignedMediumInteger('forum_id')->default(0)->index('forum_id');
            $table->unsignedMediumInteger('poster_id')->default(0)->index('poster_id');
            $table->unsignedMediumInteger('icon_id')->default(0);
            $table->string('poster_ip', 40)->default('')->index('poster_ip');
            $table->unsignedInteger('post_time')->default(0);
            $table->unsignedTinyInteger('post_approved')->default(1)->index('post_approved');
            $table->unsignedTinyInteger('post_reported')->default(0);
            $table->unsignedTinyInteger('enable_bbcode')->default(1);
            $table->unsignedTinyInteger('enable_smilies')->default(1);
            $table->unsignedTinyInteger('enable_magic_url')->default(1);
            $table->unsignedTinyInteger('enable_sig')->default(1);
            $table->string('post_username')->default('')->index('post_username');
            $table->string('post_subject')->default('');
            $table->mediumText('post_text');
            $table->string('post_checksum', 32)->default('');
            $table->unsignedTinyInteger('post_attachment')->default(0);
            $table->string('bbcode_bitfield')->default('');
            $table->string('bbcode_uid', 8)->default('');
            $table->unsignedTinyInteger('post_postcount')->default(1);
            $table->unsignedInteger('post_edit_time')->default(0);
            $table->string('post_edit_reason')->default('');
            $table->unsignedMediumInteger('post_edit_user')->default(0);
            $table->unsignedSmallInteger('post_edit_count')->default(0);
            $table->unsignedTinyInteger('post_edit_locked')->default(0);

            $table->index(['topic_id', 'post_time'], 'tid_post_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_posts');
    }
};
