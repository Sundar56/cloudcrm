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
        Schema::create('phpbb_search_wordmatch', function (Blueprint $table) {
            $table->unsignedMediumInteger('post_id')->default(0)->index('post_id');
            $table->unsignedMediumInteger('word_id')->default(0)->index('word_id');
            $table->unsignedTinyInteger('title_match')->default(0);

            $table->unique(['word_id', 'post_id', 'title_match'], 'unq_mtch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_search_wordmatch');
    }
};
