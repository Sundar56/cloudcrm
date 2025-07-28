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
        Schema::create('phpbb_topics_watch', function (Blueprint $table) {
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->unsignedTinyInteger('notify_status')->default(0)->index('notify_stat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_topics_watch');
    }
};
