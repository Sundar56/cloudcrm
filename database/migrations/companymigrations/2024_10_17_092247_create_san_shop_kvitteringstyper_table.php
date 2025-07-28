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
        Schema::create('san_shop_kvitteringstyper', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('overskrift')->nullable();
            $table->text('kvitterings_nummer_betegnelse')->nullable()->comment('fx. "Ordrenr." eller "Fakturanr."');
            $table->text('tekst1')->nullable();
            $table->text('tekst2')->nullable();
            $table->text('tekst3')->nullable();
            $table->text('tekst4')->nullable();
            $table->text('tekst5')->nullable();
            $table->text('tekst6')->nullable();
            $table->tinyInteger('usynlig')->default(0);
            $table->boolean('language_id')->default(true);
            $table->string('type', 45)->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_kvitteringstyper');
    }
};
