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
        Schema::create('san_shop_undersider', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('overside_id');
            $table->text('navn')->nullable();
            $table->text('indhold')->nullable();
            $table->integer('count')->default(0);
            $table->integer('sortering')->default(50);
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_undersider');
    }
};
