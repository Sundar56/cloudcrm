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
        Schema::create('cloud_crm_custklip', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cr_id')->nullable();
            $table->integer('kkId')->index('fk_cloud_crm_custklip_cloud_crm_firmainfo1');
            $table->integer('klipId')->index('fk_cloud_crm_custklip_cloud_crm_klippekort1');
            $table->integer('antal');
            $table->integer('resterende');
            $table->string('comment', 250)->default('');
            $table->integer('hidden')->default(0);
            $table->text('oprettet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_custklip');
    }
};
