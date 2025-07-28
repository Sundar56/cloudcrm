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
        Schema::create('san_shop_rmatekst', function (Blueprint $table) {
            $table->increments('id');
            $table->text('rmanummer')->nullable();
            $table->text('beskrivelse')->nullable();
            $table->text('ansvarlig')->nullable();
            $table->text('tidspunkt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_rmatekst');
    }
};
