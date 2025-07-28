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
        Schema::create('phpbb_topics_posted', function (Blueprint $table) {
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedMediumInteger('topic_id')->default(0);
            $table->unsignedTinyInteger('topic_posted')->default(0);

            $table->primary(['user_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_topics_posted');
    }
};
