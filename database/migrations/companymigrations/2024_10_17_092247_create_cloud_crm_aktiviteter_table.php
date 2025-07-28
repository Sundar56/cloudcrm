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
        Schema::create('cloud_crm_aktiviteter', function (Blueprint $table) {
            $table->comment('Indeholder alle aktiviteter (del-opgaver). Er knyttet til en');
            $table->integer('id', true);
            $table->integer('sagId')->default(0)->index('fk_cloud_crm_aktiviteter_cloud_crm_sager1')->comment('IDet på den tilknyttede sag. Skal udfyldes.');
            $table->integer('kundeId')->default(0);
            $table->integer('kontaktId')->default(0);
            $table->integer('oprettetSsoId')->default(0)->index('fk_cloud_crm_aktiviteter_cloud_sso_users2')->comment('SSO IDet på den person, der oprettede aktiviteten');
            $table->integer('oprettetDato')->default(0)->comment('Dato for oprettelse i unix timestamp format.');
            $table->integer('startTidspunkt')->nullable()->comment('Starttidspunkt for aktiviteten. Noteres i unix timestamp format.');
            $table->integer('slutTidspunkt')->nullable()->comment('Sluttidspunkt for aktiviteten. Noteres i UNIX timestampformat.');
            $table->integer('aktivitetsType')->default(0);
            $table->integer('sagsType')->default(0);
            $table->integer('sagsStatus')->default(0)->comment('Dette er ikke et felt til sagsStatus, men derimod aktivitetsStatus.');
            $table->text('emne')->nullable()->comment('Emnet på aktiviteten');
            $table->text('beskrivelse')->nullable()->comment('Nærmere beskrivelse på aktiviteten.');
            $table->tinyInteger('usynlig')->default(0)->comment('0 = synlig, 1 = usynlig/slettet.');
            $table->integer('deadline')->default(0);
            $table->boolean('urgent')->default(false);
            $table->string('customerType', 15)->nullable()->default('company');
            $table->integer('opened')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_aktiviteter');
    }
};
