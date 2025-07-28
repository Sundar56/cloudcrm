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
        Schema::create('phpbb_config', function (Blueprint $table) {
            $table->string('config_name')->default('')->primary();
            $table->string('config_value')->default('');
            $table->unsignedTinyInteger('is_dynamic')->default(0)->index('is_dynamic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_config');
    }
};
