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
        Schema::create('san_shop_emailskabeloner', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sender_name')->default('');
            $table->string('sender_email')->default('');
            $table->string('shortname')->nullable()->default('');
            $table->text('emne')->nullable()->comment('E-mailens emne (subject)');
            $table->text('indhold')->nullable()->comment('E-mailens indhold. Mailen kan indeholder variabler, der angives ###variabelnavn###');
            $table->tinyInteger('usynlig')->default(0)->comment('0 = synlig. 1 = usynlig.');
            $table->integer('language_id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_emailskabeloner');
    }
};
