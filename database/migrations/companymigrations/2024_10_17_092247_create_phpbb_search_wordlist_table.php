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
        Schema::create('phpbb_search_wordlist', function (Blueprint $table) {
            $table->mediumIncrements('word_id');
            $table->string('word_text')->default('')->unique('wrd_txt');
            $table->unsignedTinyInteger('word_common')->default(0);
            $table->unsignedMediumInteger('word_count')->default(0)->index('wrd_cnt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_search_wordlist');
    }
};
