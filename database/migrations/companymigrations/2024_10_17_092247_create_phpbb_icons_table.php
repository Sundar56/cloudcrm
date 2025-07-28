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
        Schema::create('phpbb_icons', function (Blueprint $table) {
            $table->mediumIncrements('icons_id');
            $table->string('icons_url')->default('');
            $table->tinyInteger('icons_width')->default(0);
            $table->tinyInteger('icons_height')->default(0);
            $table->unsignedMediumInteger('icons_order')->default(0);
            $table->unsignedTinyInteger('display_on_posting')->default(1)->index('display_on_posting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_icons');
    }
};
