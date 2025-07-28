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
        Schema::create('cloud_crm_leads', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('firmanavn', 100)->default('');
            $table->string('telefon', 50)->default('');
            $table->string('kundetype', 25)->nullable()->index('fk_cloud_crm_firmainfo_cloud_crm_kundetyper1');
            $table->string('kundenummer', 100);
            $table->string('cvr', 12)->default('');
            $table->string('ean', 25)->default('');
            $table->string('fakturaMail', 50)->default('');
            $table->text('fakturaAdresse')->nullable();
            $table->string('fakturaPostnummer', 10)->default('0');
            $table->text('fakturaBy')->nullable();
            $table->integer('kontaktId')->default(0)->index('fk_cloud_crm_firmainfo_cloud_crm_kontaktpersoner1')->comment('primær kontaktperson iid');
            $table->text('webside')->nullable();
            $table->text('kundeBeskrivelse')->nullable();
            $table->integer('hidden')->default(0);
            $table->string('prisgruppe', 2)->nullable()->default('A');
            $table->string('category', 2)->default('C');
            $table->dateTime('createdAt')->nullable();
            $table->boolean('qualified')->nullable()->default(false);
            $table->integer('country_id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_leads');
    }
};
