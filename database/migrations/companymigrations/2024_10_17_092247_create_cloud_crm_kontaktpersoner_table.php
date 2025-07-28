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
        Schema::create('cloud_crm_kontaktpersoner', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('navn', 50)->default('');
            $table->string('titel', 50)->default('');
            $table->string('telefon', 15)->default('');
            $table->string('mobil', 50)->default('');
            $table->string('country_id', 50);
            $table->string('email')->default('');
            $table->string('password', 40)->nullable();
            $table->text('adresse')->nullable();
            $table->text('city')->nullable();
            $table->integer('postnummer')->default(0);
            $table->text('beskrivelse')->nullable();
            $table->text('billede')->nullable();
            $table->boolean('spaerret')->default(false)->comment('1=kontaktpersonen er spærret,0=kontaktpersonen er ikke spærret');
            $table->integer('firmaId')->index('fk_cloud_crm_kontaktpersoner_cloud_crm_firmainfo1');
            $table->integer('hidden')->default(0);
            $table->string('brugernavn')->nullable()->default('');
            $table->integer('phpbbid')->nullable()->comment('Hvis brugeren er tilknyttet en phpbb-brugerkonto, er IDet angivet her. Ellers er den NULL.');
            $table->boolean('resetPassword')->nullable()->default(false)->comment('Hvis \'resetPassword\' er 1 betyder det, at kontaktpersonen har modtaget en e-mail med link og tilladelse til at nulstille adgangskoden. Hvis 0 må adgangskoden ikke nulstilles.');
            $table->dateTime('createdAt')->nullable();

            $table->index(['firmaId'], 'fk_cloud_crm_kontaktpersoner_cloud_crm_firmainfo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_kontaktpersoner');
    }
};
