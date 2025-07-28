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
        Schema::create('phpbb_modules', function (Blueprint $table) {
            $table->mediumIncrements('module_id');
            $table->unsignedTinyInteger('module_enabled')->default(1)->index('module_enabled');
            $table->unsignedTinyInteger('module_display')->default(1);
            $table->string('module_basename')->default('');
            $table->string('module_class', 10)->default('');
            $table->unsignedMediumInteger('parent_id')->default(0);
            $table->unsignedMediumInteger('left_id')->default(0);
            $table->unsignedMediumInteger('right_id')->default(0);
            $table->string('module_langname')->default('');
            $table->string('module_mode')->default('');
            $table->string('module_auth')->default('');

            $table->index(['module_class', 'left_id'], 'class_left_id');
            $table->index(['left_id', 'right_id'], 'left_right_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_modules');
    }
};
