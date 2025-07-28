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
        Schema::create('cloud_crm_sager', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pseudo_id_sales')->nullable();
            $table->integer('pseudo_id_service')->nullable();
            $table->text('emne')->nullable();
            $table->text('beskrivelse')->nullable();
            $table->integer('statusId')->nullable()->default(0);
            $table->integer('deadline')->nullable()->default(0)->comment('unixtimestamp');
            $table->integer('kundeId')->nullable()->default(0)->index('fk_cloud_crm_salgssager_cloud_crm_firmainfo1');
            $table->integer('kontaktId')->default(0)->comment('Knytter en kontaktpersons ID til en sag.');
            $table->integer('startdato')->nullable()->default(0)->comment('unixtimestamp');
            $table->integer('ansvarligSsoId')->nullable()->default(0)->index('fk_cloud_crm_salgssager_cloud_sso_users1')->comment('ID på den ansvarlige bruger fra JDN SSO');
            $table->integer('budget')->nullable()->default(0)->comment('Hvad projektet forventes at beløbe sig i (angives i ører).');
            $table->text('kravTilTilbud')->nullable();
            $table->integer('oprettetSsoId')->nullable()->default(0)->index('fk_cloud_crm_salgssager_cloud_sso_users2')->comment('ID fra JDN SSO på hvilken bruger der oprettede sagen');
            $table->integer('sidstAendretSsoId')->nullable()->default(0)->index('fk_cloud_crm_salgssager_cloud_sso_users3')->comment('ID fra JDN SSO på hvilken bruger der sidst ændrede sagen');
            $table->dateTime('lastEditedTime')->nullable();
            $table->integer('sagsType');
            $table->boolean('slettet')->default(false);
            $table->integer('opened')->nullable();
            $table->string('estimatedValue', 250)->nullable()->default('');
            $table->string('probability', 50)->nullable()->default('');
            $table->string('customerType', 50)->default('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_sager');
    }
};
