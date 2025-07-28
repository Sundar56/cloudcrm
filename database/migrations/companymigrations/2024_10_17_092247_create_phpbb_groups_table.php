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
        Schema::create('phpbb_groups', function (Blueprint $table) {
            $table->mediumIncrements('group_id');
            $table->tinyInteger('group_type')->default(1);
            $table->unsignedTinyInteger('group_founder_manage')->default(0);
            $table->unsignedTinyInteger('group_skip_auth')->default(0);
            $table->string('group_name')->default('');
            $table->text('group_desc');
            $table->string('group_desc_bitfield')->default('');
            $table->unsignedInteger('group_desc_options')->default(7);
            $table->string('group_desc_uid', 8)->default('');
            $table->unsignedTinyInteger('group_display')->default(0);
            $table->string('group_avatar')->default('');
            $table->tinyInteger('group_avatar_type')->default(0);
            $table->unsignedSmallInteger('group_avatar_width')->default(0);
            $table->unsignedSmallInteger('group_avatar_height')->default(0);
            $table->unsignedMediumInteger('group_rank')->default(0);
            $table->string('group_colour', 6)->default('');
            $table->unsignedMediumInteger('group_sig_chars')->default(0);
            $table->unsignedTinyInteger('group_receive_pm')->default(0);
            $table->unsignedMediumInteger('group_message_limit')->default(0);
            $table->unsignedMediumInteger('group_max_recipients')->default(0);
            $table->unsignedTinyInteger('group_legend')->default(1);

            $table->index(['group_legend', 'group_name'], 'group_legend_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_groups');
    }
};
