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
        Schema::create('san_shop_partnerlogins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('brugernavn')->nullable();
            $table->text('adgangskode')->nullable();
            $table->string('prisgruppe', 2)->default('A');
            $table->integer('kreditdage');
            $table->text('kreditdage_enhed')->nullable();
            $table->text('kundetype')->nullable()->comment('"partner" eller "slutkunde"');
            $table->tinyInteger('reset_password')->default(0)->comment('Hvis \'reset_password\' er 1 betyder det, at kunden har modtaget en e-mail med link og tilladelse til at nulstille adgangskoden. Hvis 0 må adgangskoden ikke nulstilles.');
            $table->tinyInteger('new_customer')->default(1);
            $table->tinyInteger('new_customer_credit_requested')->default(0);
            $table->text('new_customer_temp_password_plain')->nullable();
            $table->tinyInteger('usynlig')->default(0);
            $table->integer('phpbbid')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_partnerlogins');
    }
};
