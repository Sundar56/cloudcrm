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
        Schema::create('cloud_crm_historik', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('historikType')->default(0)->index('fk_cloud_crm_historik_cloud_crm_historik_typer1');
            $table->text('beskrivelse')->nullable();
            $table->integer('tidspunkt')->default(0)->comment('unixtime');
            $table->integer('relationsId')->default(0)->index('fk_cloud_crm_historik_cloud_crm_aktiviteter1');
            $table->text('relationsType')->nullable()->comment('\'ordre\', \'partner\', \'partner_prisregel\', ....');
            $table->integer('ssoId')->default(0)->index('fk_cloud_crm_historik_cloud_sso_users1')->comment('Medarbejder ID fra JDN SSO på brugeren der har udført handlingen');
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_historik');
    }
};
