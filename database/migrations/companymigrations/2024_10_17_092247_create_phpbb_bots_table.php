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
        Schema::create('phpbb_bots', function (Blueprint $table) {
            $table->mediumIncrements('bot_id');
            $table->unsignedTinyInteger('bot_active')->default(1)->index('bot_active');
            $table->string('bot_name')->default('');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->string('bot_agent')->default('');
            $table->string('bot_ip')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_bots');
    }
};
