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
        Schema::create('phpbb_forums_access', function (Blueprint $table) {
            $table->unsignedMediumInteger('forum_id')->default(0);
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->char('session_id', 32)->default('');

            $table->primary(['forum_id', 'user_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_forums_access');
    }
};
