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
        Schema::create('san_shop_kurv', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('ordreid')->nullable()->default(0);
            $table->integer('userid')->nullable()->default(0);
            $table->text('vareid')->nullable()->comment('Varenummer (dvs. faktisk ikke vare ID\'et) md5 krypteret ');
            $table->text('varenummer')->nullable()->comment('Varenummeret i klar tekst');
            $table->text('varenavn')->nullable();
            $table->text('pris')->nullable()->comment('pris uden moms i ører');
            $table->text('antal')->nullable();
            $table->mediumInteger('sendt')->default(0)->comment('Ved dellevering: Angiver hvor mange enheder der er sendt. Ved afhentning: angiver hvor mange varer der er afhentet.');
            $table->mediumInteger('tidligere_sendt')->default(0)->comment('Hvis der sendes dellevering/afhentning, så angiver dette felt, om der tidligere er leveret nogle enheder af denne vare. Hvis der er, så skal disse ikke medtages i mailen til kunden igen.');
            $table->integer('afhentnings_klar')->default(0)->comment('Hvor mange varer der er Klar til afhentning');
            $table->text('bestilt')->nullable();
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_kurv');
    }
};
