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
        Schema::create('phpbb_styles_theme', function (Blueprint $table) {
            $table->mediumIncrements('theme_id');
            $table->string('theme_name')->default('')->unique('theme_name');
            $table->string('theme_copyright')->default('');
            $table->string('theme_path', 100)->default('');
            $table->unsignedTinyInteger('theme_storedb')->default(0);
            $table->unsignedInteger('theme_mtime')->default(0);
            $table->mediumText('theme_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles_theme');
    }
};
