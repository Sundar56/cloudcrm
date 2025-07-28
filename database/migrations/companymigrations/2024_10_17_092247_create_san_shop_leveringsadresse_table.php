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
        Schema::create('san_shop_leveringsadresse', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ordrenummer');
            $table->text('firmanavn')->nullable();
            $table->text('navn')->nullable();
            $table->text('adresse')->nullable();
            $table->text('postnr')->nullable();
            $table->text('city')->nullable();
            $table->text('email')->nullable();
            $table->text('cvrnummer')->nullable();
            $table->text('eannummer')->nullable();
            $table->text('rekvisition')->nullable();
            $table->text('telefonnummer')->nullable();
            $table->text('kommentar')->nullable();
            $table->integer('country_id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_leveringsadresse');
    }
};
