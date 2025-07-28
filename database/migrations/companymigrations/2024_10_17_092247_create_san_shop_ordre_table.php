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
        Schema::create('san_shop_ordre', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_id')->default(0);
            $table->integer('userid')->default(0)->comment('Kontaktperson id\'et, hvis ordren er lagt af en kunde med kundekonto');
            $table->text('navn')->nullable();
            $table->text('adresse')->nullable();
            $table->text('postnr')->nullable();
            $table->text('city')->nullable();
            $table->text('email')->nullable();
            $table->text('email2')->nullable();
            $table->text('firmanavn')->nullable();
            $table->text('betingelser')->nullable();
            $table->text('cvrnummer')->nullable();
            $table->text('eannummer')->nullable();
            $table->text('telefon')->nullable();
            $table->integer('country_id')->nullable()->default(1);
            $table->text('nyhedsbrev')->nullable();
            $table->text('kommentar')->nullable();
            $table->text('betalingsmetode')->nullable();
            $table->text('transportmetode')->nullable();
            $table->text('bestilt')->nullable();
            $table->text('ordrenbehandlet')->nullable();
            $table->text('transportpris')->nullable();
            $table->text('betalingspris')->nullable();
            $table->text('ekstern_faktura_gebyr')->nullable();
            $table->text('finansieringstillaeg')->nullable();
            $table->text('dato')->nullable();
            $table->time('tidspunkt')->nullable();
            $table->text('rekvisition')->nullable();
            $table->text('leveringsadresse')->nullable()->comment('Kan enten være \'identisk\' eller \'forskellig\'');
            $table->string('leverings_dato', 200)->nullable();
            $table->unsignedInteger('transaktionsnummer')->nullable()->default(0);
            $table->text('kortnummer')->nullable()->comment('Et forkortet/anonymiseret kortnummer (betalingskort), sådan at det kan fremgå af fakturaen.');
            $table->text('refurl')->nullable();
            $table->integer('fakturaid')->default(0)->comment('kun et id der bliver brugt visuelt');
            $table->text('kvittering_faktura')->nullable();
            $table->text('kvittering_ordrebekræftelse')->nullable();
            $table->text('kvittering_bestilling')->nullable();
            $table->boolean('admin_set')->default(false);
            $table->tinyInteger('status')->default(0);
            $table->string('betalt', 4)->nullable();
            $table->integer('kreditkortBetalingBekraeftet')->default(0)->comment('Feltet er 0, hvis det endnu ikke er bekræftet, at transaktionen ligger på gatewayen. Hvis det er bekræftet, så angives unix timestamp for hvornår det blev bekræftet.');
            $table->boolean('kreditPaaDenneOrdre')->default(false)->comment('Hvis det er en partnerkunde med kredit der lægger ordren, så sættes dette felt automatisk til 1 (ved kontooverførsel og faktura). Det er ydermere muligt manuelt at give en ordre kredit i sitemanager.');
            $table->tinyInteger('kreditdage')->default(0)->comment('Kreditdage ved bestilling');
            $table->tinyInteger('kreditdageDispensation')->default(0)->comment('hvis kunden ikke er kreditkunde, så er det stadig muligt at ændre ordren til Faktura, fra sitemanager. Man får i så fald lov til at angive hvor mange kreditdage man vil give kunden på ordren. Dette felt godtager KUN kreditdage (ikke løbende måned+x dage).');
            $table->string('prisgruppeSeneste', 1)->default('0');
            $table->boolean('fakturering')->default(false)->comment('•	Ikke faktuereret - 0 • Faktura indtastet - 1 • Faktura afsendt - 2');
            $table->boolean('alleVarerAfsendt')->default(false)->comment('hvis alle varer er afsendt på ordren, så sættes denne til 1. Gælder kun forsendelsesordrer. Afhentning ordrer er altid 0.');
            $table->integer('pris')->default(0);
            $table->integer('dato_unixtime')->default(0);
            $table->tinyInteger('slettet')->default(0)->comment('0 = ikke slettet, 1 = slettet (dette felt svarer til usynlig-feltet andre steder)');
            $table->boolean('bestillings_process')->default(true)->comment('Hvor langt kunden er kommet i deres bestillings process (gamle ordrer inden der blev lagt inden indførelse af dette felt har værdien 0)');
            $table->string('browser', 60)->nullable();
            $table->string('ip_til_land', 2)->nullable();
            $table->text('kundetype')->nullable()->comment('kundetype på bestillingstidspunktet. Udfyldes også automatisk af systemet, hvis kunden ikke er logget ind, men systemet kan finde et match på baggrund af email og cvr nr. ');
            $table->smallInteger('loggetIndPaaKundekonto')->nullable()->default(0)->comment('Angiver om kunden var logget ind med en kundekonto på bestillingstidspunktet');
            $table->decimal('kontantrabat', 12)->default(0)->comment('Hvor mange procent af ordrebeløbet inkl. fragt der skal gives i rabat.');
            $table->integer('procentAfOrginal')->nullable()->default(0)->comment('Hvor mange kroner een procent af ordrebeløbet inkl. fragt er');
            $table->text('bogholderiEmail')->nullable();
            $table->integer('temp_row_updated')->nullable()->default(0);
            $table->integer('vat')->nullable()->default(25)->comment('Vat percent on this order.');
            $table->string('vatCountryCode', 5)->nullable()->default('DK');
            $table->string('phoneCode', 5)->nullable()->default('+45');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_ordre');
    }
};
