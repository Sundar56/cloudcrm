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
        Schema::create('san_shop_trackntrace', function (Blueprint $table) {
            $table->comment('Indeholder de track\'n\'trace-numre der er knyttet til en ordr');
            $table->integer('id', true);
            $table->integer('ordreid')->nullable()->default(0);
            $table->text('trackntrace_nummer')->nullable();
            $table->integer('dato_unixtime')->nullable()->default(0);
            $table->tinyInteger('old_number')->default(0)->comment('Hvis der tilføjes nye tt numre til en ordre, så sættes alle tidligere tilføjede tt numre til old_number=1 så de ikke gensendes til kunden.');
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_trackntrace');
    }
};
