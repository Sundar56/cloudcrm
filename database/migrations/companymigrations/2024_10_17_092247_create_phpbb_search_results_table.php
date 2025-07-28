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
        Schema::create('phpbb_search_results', function (Blueprint $table) {
            $table->string('search_key', 32)->default('')->primary();
            $table->unsignedInteger('search_time')->default(0);
            $table->mediumText('search_keywords');
            $table->mediumText('search_authors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_search_results');
    }
};
