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
        Schema::create('san_shop_fakturaslettet', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ordreid')->nullable()->default(0);
            $table->text('kvittering_bestilling')->nullable();
            $table->text('kvittering_faktura')->nullable();
            $table->text('kvittering_ordrebekræftelse')->nullable();
            $table->unsignedInteger('admin_set')->nullable()->default(0);
            $table->tinyInteger('status')->default(0)->comment('ID på status i san_shop_faktura_statustyper');
            $table->text('betalt')->nullable();
            $table->integer('kreditkortBetalingBekraeftet')->default(0)->comment('Feltet er 0, hvis det endnu ikke er bekræftet, at transaktionen ligger på gatewayen. Hvis det er bekræftet, så angives unix timestamp for hvornår det blev bekræftet.');
            $table->boolean('kreditPaaDenneOrdre')->default(false)->comment('Hvis det er en partnerkunde med kredit der lægger ordren, så sættes dette felt automatisk til 1 (ved kontooverførsel og faktura). Det er ydermere muligt manuelt at give en ordre kredit i sitemanager.');
            $table->tinyInteger('kreditdageDispensation')->default(0)->comment('hvis kunden ikke er kreditkunde, så er det stadig muligt at ændre ordren til Faktura, fra sitemanager. Man får i så fald lov til at angive hvor mange kreditdage man vil give kunden på ordren. Dette felt godtager KUN kreditdage (ikke løbende måned+x dage).');
            $table->boolean('fakturering')->default(false)->comment('•	Ikke faktuereret   - 0 •	Faktura indtastet -  1 •	Faktura afsendt   -  2 ');
            $table->boolean('alleVarerAfsendt')->default(false)->comment('hvis alle varer er afsendt på ordren, så sættes denne til 1. Gælder kun forsendelsesordrer. Afhentning ordrer er altid 0.');
            $table->text('pris')->nullable();
            $table->text('dato')->nullable();
            $table->integer('dato_unixtime')->default(0);
            $table->tinyInteger('slettet')->default(0)->comment('0 = ikke slettet, 1 = slettet (dette felt svarer til usynlig-feltet andre steder)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_fakturaslettet');
    }
};
