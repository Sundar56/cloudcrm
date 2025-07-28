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
        Schema::create('phpbb_banlist', function (Blueprint $table) {
            $table->mediumIncrements('ban_id');
            $table->unsignedMediumInteger('ban_userid')->default(0);
            $table->string('ban_ip', 40)->default('');
            $table->string('ban_email', 100)->default('');
            $table->unsignedInteger('ban_start')->default(0);
            $table->unsignedInteger('ban_end')->default(0)->index('ban_end');
            $table->unsignedTinyInteger('ban_exclude')->default(0);
            $table->string('ban_reason')->default('');
            $table->string('ban_give_reason')->default('');

            $table->index(['ban_email', 'ban_exclude'], 'ban_email');
            $table->index(['ban_ip', 'ban_exclude'], 'ban_ip');
            $table->index(['ban_userid', 'ban_exclude'], 'ban_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_banlist');
    }
};
