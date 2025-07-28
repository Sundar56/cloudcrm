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
        Schema::create('san_shop_partneroplysninger', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('firmanavn')->nullable();
            $table->text('navn')->nullable();
            $table->text('adresse')->nullable();
            $table->text('postnr')->nullable();
            $table->text('city')->nullable();
            $table->text('email')->nullable();
            $table->text('cvrnummer')->nullable();
            $table->text('eannummer')->nullable();
            $table->text('telefonnummer')->nullable();
            $table->integer('partner_id');
            $table->text('oplysninger_type')->nullable()->comment('\'faktureringsoplysninger\' eller \'leveringsadresse\'');
            $table->tinyInteger('usynlig')->default(0);
            $table->string('kundenummer', 90)->nullable()->comment('Kundeummer i AirBOSS. Kundenummer er kundens CVR nummer, jo mindre der er flere kunder med samme CVRnr. (fx offentlige institutioner), da kaldes de øvrige CVRNUMMER-1, CVRNUMMER-2 osv.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_partneroplysninger');
    }
};
