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
        Schema::create('san_shop_ordre_leveringer', function (Blueprint $table) {
            $table->comment('Afhentninger og leveringer');
            $table->integer('id', true);
            $table->integer('ordreid')->nullable();
            $table->integer('datoUnixtime')->nullable();
            $table->string('typeLevering', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_ordre_leveringer');
    }
};
