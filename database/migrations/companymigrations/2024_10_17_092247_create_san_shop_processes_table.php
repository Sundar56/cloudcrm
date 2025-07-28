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
        Schema::create('san_shop_processes', function (Blueprint $table) {
            $table->comment('Indeholder status om udførsler der er i gang, fx om et PHP script er ved at afslutte en ordre (generere faktura osv.), så ikke et andet kald til samme PHP script går ind og gør det samme.	');
            $table->integer('id', true)->unique('id_unique');
            $table->string('process_name', 45)->nullable()->default('')->comment('Fx \'completeOrder\'');
            $table->string('process_attribute', 45)->nullable()->default('')->comment('Fx idet på den ordre man er ved at behandle');
            $table->string('process_owner', 45)->nullable()->default('')->comment('Fx. et unikt id til at identificere den pågældende proces');
            $table->timestamp('time_started')->useCurrent();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_processes');
    }
};
