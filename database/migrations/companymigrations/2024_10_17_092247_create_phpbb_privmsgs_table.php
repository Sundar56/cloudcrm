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
        Schema::create('phpbb_privmsgs', function (Blueprint $table) {
            $table->mediumIncrements('msg_id');
            $table->unsignedMediumInteger('root_level')->default(0)->index('root_level');
            $table->unsignedMediumInteger('author_id')->default(0)->index('author_id');
            $table->unsignedMediumInteger('icon_id')->default(0);
            $table->string('author_ip', 40)->default('')->index('author_ip');
            $table->unsignedInteger('message_time')->default(0)->index('message_time');
            $table->unsignedTinyInteger('enable_bbcode')->default(1);
            $table->unsignedTinyInteger('enable_smilies')->default(1);
            $table->unsignedTinyInteger('enable_magic_url')->default(1);
            $table->unsignedTinyInteger('enable_sig')->default(1);
            $table->string('message_subject')->default('');
            $table->mediumText('message_text');
            $table->string('message_edit_reason')->default('');
            $table->unsignedMediumInteger('message_edit_user')->default(0);
            $table->unsignedTinyInteger('message_attachment')->default(0);
            $table->string('bbcode_bitfield')->default('');
            $table->string('bbcode_uid', 8)->default('');
            $table->unsignedInteger('message_edit_time')->default(0);
            $table->unsignedSmallInteger('message_edit_count')->default(0);
            $table->text('to_address');
            $table->text('bcc_address');
            $table->unsignedTinyInteger('message_reported')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_privmsgs');
    }
};
