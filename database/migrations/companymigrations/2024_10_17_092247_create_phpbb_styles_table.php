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
        Schema::create('phpbb_styles', function (Blueprint $table) {
            $table->mediumIncrements('style_id');
            $table->string('style_name')->default('')->unique('style_name');
            $table->string('style_copyright')->default('');
            $table->unsignedTinyInteger('style_active')->default(1);
            $table->unsignedMediumInteger('template_id')->default(0)->index('template_id');
            $table->unsignedMediumInteger('theme_id')->default(0)->index('theme_id');
            $table->unsignedMediumInteger('imageset_id')->default(0)->index('imageset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles');
    }
};
