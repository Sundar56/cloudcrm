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
        Schema::create('san_shop_historik', function (Blueprint $table) {
            $table->integer('id', true);
            $table->tinyInteger('historik_type')->default(0);
            $table->text('beskrivelse')->nullable();
            $table->integer('dato_unixtime')->default(0);
            $table->integer('relations_id')->default(0);
            $table->text('relations_type')->nullable()->comment('\'ordre\', \'partner\', \'partner_prisregel\', ....');
            $table->integer('bruger_id')->default(0)->comment('Bruger ID på brugeren der har udført handlingen');
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_historik');
    }
};
