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
        Schema::create('san_shop_historik_typer', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('type_beskrivelse')->nullable();
            $table->text('fast_tekst')->nullable();
            $table->text('relations_type')->nullable()->comment('Hvilken Bestemmer hvad denne historik type skal skrive i relations_type-feltet i san_shop_historik. Fx \'ordre\', \'partner\', \'partner_prisregel\', ....');
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_historik_typer');
    }
};
