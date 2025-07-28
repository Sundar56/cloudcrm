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
        Schema::create('phpbb_styles_imageset_data', function (Blueprint $table) {
            $table->mediumIncrements('image_id');
            $table->string('image_name', 200)->default('');
            $table->string('image_filename', 200)->default('');
            $table->string('image_lang', 30)->default('');
            $table->unsignedSmallInteger('image_height')->default(0);
            $table->unsignedSmallInteger('image_width')->default(0);
            $table->unsignedMediumInteger('imageset_id')->default(0)->index('i_d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles_imageset_data');
    }
};
