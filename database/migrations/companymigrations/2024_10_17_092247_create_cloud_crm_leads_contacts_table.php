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
        Schema::create('cloud_crm_leads_contacts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('navn', 50)->nullable();
            $table->string('titel', 50)->nullable();
            $table->string('telefon', 15)->nullable();
            $table->string('mobil', 50)->nullable();
            $table->string('country_id', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->text('city')->nullable();
            $table->integer('postnummer')->default(0);
            $table->text('beskrivelse')->nullable();
            $table->text('billede')->nullable();
            $table->integer('firmaId');
            $table->integer('hidden')->default(0);
            $table->dateTime('createdAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_leads_contacts');
    }
};
