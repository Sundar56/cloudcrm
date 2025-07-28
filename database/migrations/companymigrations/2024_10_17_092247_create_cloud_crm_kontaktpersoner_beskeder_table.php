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
        Schema::create('cloud_crm_kontaktpersoner_beskeder', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fra_id');
            $table->integer('kontaktperson_id')->nullable();
            $table->text('besked');
            $table->integer('tidspunkt');
            $table->boolean('email_send')->default(false);
            $table->boolean('slettet')->default(false);
            $table->integer('lead_kontaktperson_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_kontaktpersoner_beskeder');
    }
};
