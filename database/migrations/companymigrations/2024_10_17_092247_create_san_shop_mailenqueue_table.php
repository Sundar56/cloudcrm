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
        Schema::create('san_shop_mailenqueue', function (Blueprint $table) {
            $table->integer('id', true);
            $table->mediumText('afsender_email')->nullable();
            $table->mediumText('afsender_navn')->nullable();
            $table->mediumText('til_email')->nullable();
            $table->mediumText('til_navn')->nullable();
            $table->mediumText('headers')->nullable();
            $table->mediumText('emne')->nullable();
            $table->mediumText('body')->nullable();
            $table->tinyInteger('prioritet')->nullable()->comment('Prioritering af mailafsendelse.');
            $table->boolean('html_mail')->default(false);
            $table->tinyInteger('mail_afsendt')->default(0)->comment('Er mailen afsendt eller ej? Hvis den er, så skal systemet jo ikke sende den igen.');
            $table->mediumText('dato_og_tid_for_oprettelse')->nullable();
            $table->mediumText('dato_og_tid_for_afsendelse')->nullable();
            $table->string('afsenderip')->default('0');
            $table->text('attachments')->nullable();
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_mailenqueue');
    }
};
