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
        Schema::create('phpbb_bbcodes', function (Blueprint $table) {
            $table->unsignedSmallInteger('bbcode_id')->default(0)->primary();
            $table->string('bbcode_tag', 16)->default('');
            $table->string('bbcode_helpline')->default('');
            $table->unsignedTinyInteger('display_on_posting')->default(0)->index('display_on_post');
            $table->text('bbcode_match');
            $table->mediumText('bbcode_tpl');
            $table->mediumText('first_pass_match');
            $table->mediumText('first_pass_replace');
            $table->mediumText('second_pass_match');
            $table->mediumText('second_pass_replace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_bbcodes');
    }
};
