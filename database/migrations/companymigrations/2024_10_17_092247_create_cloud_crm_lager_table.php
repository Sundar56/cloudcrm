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
        Schema::create('cloud_crm_lager', function (Blueprint $table) {
            $table->increments('id');
            $table->text('varenummer')->nullable();
            $table->text('varenavn1')->nullable();
            $table->text('varenavn2')->nullable()->comment('model nr.');
            $table->text('varenavn1_slug')->nullable();
            $table->text('varenavn2_slug')->nullable();
            $table->text('hurtigvarebeskrivelse')->nullable();
            $table->text('status')->nullable()->comment('angiver hvor mange stk. leverandøren har på lager af dette produkt (dvs. hvis status > 0 betyder det, at den er varen er på fjernlager)');
            $table->text('beskrivelse')->nullable();
            $table->integer('lager')->nullable()->default(0)->comment('bruges ikke længere');
            $table->text('enhed')->nullable();
            $table->text('vaegt')->nullable()->comment('brutto vægt');
            $table->text('gruppe')->nullable();
            $table->text('sortering1')->nullable();
            $table->text('sortering2')->nullable();
            $table->text('billede')->nullable();
            $table->unsignedInteger('rabatpct')->nullable()->default(0);
            $table->unsignedInteger('checked')->nullable()->default(2);
            $table->text('nettokg')->nullable();
            $table->unsignedInteger('lager_antal')->nullable()->comment('viser antal stk. på eget lager');
            $table->text('producent')->nullable();
            $table->unsignedBigInteger('icecat_updated')->nullable()->default(0);
            $table->string('navn', 45)->nullable();
            $table->float('pris')->unsigned()->nullable();
            $table->integer('pris2')->nullable();
            $table->integer('pris3')->nullable();
            $table->integer('pris4')->nullable();
            $table->string('lokation', 45)->nullable();
            $table->float('detail_m_moms')->nullable();
            $table->float('detail_ex_moms')->nullable();
            $table->float('forh_ex_moms')->nullable();
            $table->string('disponibel', 45)->nullable();
            $table->string('stregkode', 45)->nullable();
            $table->string('kontonr', 45)->nullable();
            $table->string('leverandor_vare', 45)->nullable();
            $table->string('leverandor_nummer', 45)->nullable();
            $table->string('producent_navn', 45)->nullable();
            $table->string('producent_modelnr', 45)->nullable();
            $table->float('netto_indkobspris')->nullable();
            $table->string('land', 45)->nullable();
            $table->float('netto_indkobspris_dkr')->nullable();
            $table->string('moms_art', 45)->nullable();
            $table->string('størrelse', 45)->nullable();
            $table->string('deleted_at', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_lager');
    }
};
