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
        Schema::create('cloud_crm_firmainfo', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('firmanavn', 100)->default('');
            $table->string('telefon', 50)->default('');
            $table->string('kundetype', 25)->nullable()->index('fk_cloud_crm_firmainfo_cloud_crm_kundetyper1');
            $table->string('kreditdage', 5)->default('0');
            $table->integer('kredittid')->default(1)->index('fk_cloud_crm_firmainfo_cloud_crm_kreditdage1');
            $table->string('kreditmax', 250)->default('');
            $table->string('kundenummer', 100)->nullable();
            $table->string('cvr', 20)->default('');
            $table->string('ean', 25)->default('');
            $table->string('fakturaMail', 50)->default('');
            $table->text('fakturaAdresse')->nullable();
            $table->string('fakturaPostnummer', 20)->default('0');
            $table->text('fakturaBy')->nullable();
            $table->integer('ssouserid')->nullable();
            $table->integer('ssopmuserid')->nullable();
            $table->integer('kontaktId')->default(0)->index('fk_cloud_crm_firmainfo_cloud_crm_kontaktpersoner1')->comment('primær kontaktperson iid');
            $table->text('webside')->nullable();
            $table->text('kundeBeskrivelse')->nullable();
            $table->integer('hidden')->default(0);
            $table->integer('blocked')->default(0);
            $table->boolean('is_new')->nullable()->default(false);
            $table->string('prisgruppe', 2)->nullable()->default('A');
            $table->boolean('requestedCredit')->default(false)->comment('Hvis et firma der har ansøgt om at få oprettet konto har angivet, at de ønsker kredit, så bliver dette felt sat til 1');
            $table->dateTime('createdAt')->nullable();
            $table->dateTime('latestBlockedAt')->nullable();
            $table->dateTime('latestUnblockedAt')->nullable();
            $table->string('category', 2)->default('C');
            $table->boolean('external_credit')->nullable()->default(false);
            $table->integer('country_id')->nullable()->default(1);
            $table->string('dynamic_solution', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_firmainfo');
    }
};
