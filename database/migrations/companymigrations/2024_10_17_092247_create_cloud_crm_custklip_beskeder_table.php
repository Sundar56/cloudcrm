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
        Schema::create('cloud_crm_custklip_beskeder', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fra_id');
            $table->integer('firmainfo_id');
            $table->text('besked');
            $table->integer('tidspunkt');
            $table->boolean('email_send');
            $table->integer('klip_trukket')->default(0);
            $table->boolean('slettet')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_custklip_beskeder');
    }
};
