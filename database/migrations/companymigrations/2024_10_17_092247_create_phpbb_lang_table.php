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
        Schema::create('phpbb_lang', function (Blueprint $table) {
            $table->tinyInteger('lang_id', true);
            $table->string('lang_iso', 30)->default('')->index('lang_iso');
            $table->string('lang_dir', 30)->default('');
            $table->string('lang_english_name', 100)->default('');
            $table->string('lang_local_name')->default('');
            $table->string('lang_author')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_lang');
    }
};
