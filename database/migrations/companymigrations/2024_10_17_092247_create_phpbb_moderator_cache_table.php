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
        Schema::create('phpbb_moderator_cache', function (Blueprint $table) {
            $table->unsignedMediumInteger('forum_id')->default(0)->index('forum_id');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->string('username')->default('');
            $table->unsignedMediumInteger('group_id')->default(0);
            $table->string('group_name')->default('');
            $table->unsignedTinyInteger('display_on_index')->default(1)->index('disp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_moderator_cache');
    }
};
