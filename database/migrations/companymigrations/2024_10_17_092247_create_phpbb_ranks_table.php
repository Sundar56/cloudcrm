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
        Schema::create('phpbb_ranks', function (Blueprint $table) {
            $table->mediumIncrements('rank_id');
            $table->string('rank_title')->default('');
            $table->unsignedMediumInteger('rank_min')->default(0);
            $table->unsignedTinyInteger('rank_special')->default(0);
            $table->string('rank_image')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_ranks');
    }
};
