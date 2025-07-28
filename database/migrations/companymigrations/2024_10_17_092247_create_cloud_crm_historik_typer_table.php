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
        Schema::create('cloud_crm_historik_typer', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('typeBeskrivelse')->nullable();
            $table->text('fastTekst')->nullable();
            $table->text('relationsType')->nullable()->comment('Hvilken Bestemmer hvad denne historik type skal skrive i relations_type-feltet i san_shop_historik. Fx \'ordre\', \'partner\', \'partner_prisregel\', ....');
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_historik_typer');
    }
};
