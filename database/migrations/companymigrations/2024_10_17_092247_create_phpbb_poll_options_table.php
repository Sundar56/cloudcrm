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
        Schema::create('phpbb_poll_options', function (Blueprint $table) {
            $table->tinyInteger('poll_option_id')->default(0)->index('poll_opt_id');
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->text('poll_option_text');
            $table->unsignedMediumInteger('poll_option_total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_poll_options');
    }
};
