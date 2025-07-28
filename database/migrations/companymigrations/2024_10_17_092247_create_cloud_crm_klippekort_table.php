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
        Schema::create('cloud_crm_klippekort', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('varenummer');
            $table->string('navn');
            $table->integer('antal');
            $table->integer('pris');
            $table->integer('hidden')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_klippekort');
    }
};
