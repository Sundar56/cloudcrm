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
        Schema::create('san_shop_ordre_notater', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ordreid')->comment('IDet på den ordre som notatet hører til.');
            $table->integer('unix_timestamp')->comment('Tidspunktet for oprettelsen af notatet i unix-timestamp-format.');
            $table->integer('brugerid')->comment('IDet på den bruger, der har oprettet notatet.');
            $table->text('notat')->comment('Notatteksten');
            $table->tinyInteger('usynlig')->default(0)->comment('0 = synlig, 1 = usynlig');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_ordre_notater');
    }
};
