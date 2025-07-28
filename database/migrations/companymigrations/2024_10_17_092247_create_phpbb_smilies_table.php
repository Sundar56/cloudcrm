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
        Schema::create('phpbb_smilies', function (Blueprint $table) {
            $table->mediumIncrements('smiley_id');
            $table->string('code', 50)->default('');
            $table->string('emotion', 50)->default('');
            $table->string('smiley_url', 50)->default('');
            $table->unsignedSmallInteger('smiley_width')->default(0);
            $table->unsignedSmallInteger('smiley_height')->default(0);
            $table->unsignedMediumInteger('smiley_order')->default(0);
            $table->unsignedTinyInteger('display_on_posting')->default(1)->index('display_on_post');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_smilies');
    }
};
