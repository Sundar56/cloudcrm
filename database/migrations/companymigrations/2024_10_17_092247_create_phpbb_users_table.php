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
        Schema::create('phpbb_users', function (Blueprint $table) {
            $table->mediumIncrements('user_id');
            $table->tinyInteger('user_type')->default(0)->index('user_type');
            $table->unsignedMediumInteger('group_id')->default(3);
            $table->mediumText('user_permissions');
            $table->unsignedMediumInteger('user_perm_from')->default(0);
            $table->string('user_ip', 40)->default('');
            $table->unsignedInteger('user_regdate')->default(0);
            $table->string('username')->default('');
            $table->string('username_clean')->default('')->unique('username_clean');
            $table->string('user_password', 40)->default('');
            $table->unsignedInteger('user_passchg')->default(0);
            $table->unsignedTinyInteger('user_pass_convert')->default(0);
            $table->string('user_email', 100)->default('');
            $table->bigInteger('user_email_hash')->default(0)->index('user_email_hash');
            $table->string('user_birthday', 10)->default('')->index('user_birthday');
            $table->unsignedInteger('user_lastvisit')->default(0);
            $table->unsignedInteger('user_lastmark')->default(0);
            $table->unsignedInteger('user_lastpost_time')->default(0);
            $table->string('user_lastpage', 200)->default('');
            $table->string('user_last_confirm_key', 10)->default('');
            $table->unsignedInteger('user_last_search')->default(0);
            $table->tinyInteger('user_warnings')->default(0);
            $table->unsignedInteger('user_last_warning')->default(0);
            $table->tinyInteger('user_login_attempts')->default(0);
            $table->tinyInteger('user_inactive_reason')->default(0);
            $table->unsignedInteger('user_inactive_time')->default(0);
            $table->unsignedMediumInteger('user_posts')->default(0);
            $table->string('user_lang', 30)->default('');
            $table->decimal('user_timezone', 5)->default(0);
            $table->unsignedTinyInteger('user_dst')->default(0);
            $table->string('user_dateformat', 30)->default('d M Y H:i');
            $table->unsignedMediumInteger('user_style')->default(0);
            $table->unsignedMediumInteger('user_rank')->default(0);
            $table->string('user_colour', 6)->default('');
            $table->integer('user_new_privmsg')->default(0);
            $table->integer('user_unread_privmsg')->default(0);
            $table->unsignedInteger('user_last_privmsg')->default(0);
            $table->unsignedTinyInteger('user_message_rules')->default(0);
            $table->integer('user_full_folder')->default(-3);
            $table->unsignedInteger('user_emailtime')->default(0);
            $table->unsignedSmallInteger('user_topic_show_days')->default(0);
            $table->string('user_topic_sortby_type', 1)->default('t');
            $table->string('user_topic_sortby_dir', 1)->default('d');
            $table->unsignedSmallInteger('user_post_show_days')->default(0);
            $table->string('user_post_sortby_type', 1)->default('t');
            $table->string('user_post_sortby_dir', 1)->default('a');
            $table->unsignedTinyInteger('user_notify')->default(0);
            $table->unsignedTinyInteger('user_notify_pm')->default(1);
            $table->tinyInteger('user_notify_type')->default(0);
            $table->unsignedTinyInteger('user_allow_pm')->default(1);
            $table->unsignedTinyInteger('user_allow_viewonline')->default(1);
            $table->unsignedTinyInteger('user_allow_viewemail')->default(1);
            $table->unsignedTinyInteger('user_allow_massemail')->default(1);
            $table->unsignedInteger('user_options')->default(230271);
            $table->string('user_avatar')->default('');
            $table->tinyInteger('user_avatar_type')->default(0);
            $table->unsignedSmallInteger('user_avatar_width')->default(0);
            $table->unsignedSmallInteger('user_avatar_height')->default(0);
            $table->mediumText('user_sig');
            $table->string('user_sig_bbcode_uid', 8)->default('');
            $table->string('user_sig_bbcode_bitfield')->default('');
            $table->string('user_from', 100)->default('');
            $table->string('user_icq', 15)->default('');
            $table->string('user_aim')->default('');
            $table->string('user_yim')->default('');
            $table->string('user_msnm')->default('');
            $table->string('user_jabber')->default('');
            $table->string('user_website', 200)->default('');
            $table->text('user_occ');
            $table->text('user_interests');
            $table->string('user_actkey', 32)->default('');
            $table->string('user_newpasswd', 40)->default('');
            $table->string('user_form_salt', 32)->default('');
            $table->unsignedTinyInteger('user_new')->default(1);
            $table->tinyInteger('user_reminded')->default(0);
            $table->unsignedInteger('user_reminded_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_users');
    }
};
