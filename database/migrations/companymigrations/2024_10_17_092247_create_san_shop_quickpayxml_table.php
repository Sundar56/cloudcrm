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
        Schema::create('san_shop_quickpayxml', function (Blueprint $table) {
            $table->comment('Gemmer resultater fra kommunikation med QP API');
            $table->integer('id', true);
            $table->integer('tidspunktUnixtime');
            $table->integer('ordreid')->default(0)->comment('fra san_shop_faktura med gammel struktur (hvor der både fandtes san_shop_ordre og san_shop_faktura)');
            $table->text('xml')->nullable()->comment('XML resultat fra Quickpay API');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_quickpayxml');
    }
};
