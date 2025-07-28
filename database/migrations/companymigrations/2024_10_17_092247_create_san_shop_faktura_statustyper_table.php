<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Traits\SqlFileRunner;

return new class extends Migration
{
    use SqlFileRunner;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('san_shop_faktura_statustyper', function (Blueprint $table) {
            $table->integer('id', true);
            $table->tinyInteger('status_id')->default(0)->comment('En slags kategori til statusen. Er den under aktive ordre(0-9), afsendte(10-19) eller slettede(20-29)?');
            $table->text('status_beskrivelse')->nullable()->comment('Navn på denne status, dvs. hvad der vises i sitemanager udfor ordren når denne status er valgt');
            $table->text('status_actionnavn')->nullable()->comment('Hvilket navn der skal vises under handlinger-menuen på status\'en');
            $table->text('status_forklaring')->nullable()->comment('Hjælpetekst der forklarer hvornår status\'en skal bruges');
            $table->text('kunde_vis_navn')->nullable()->comment('Dette navn vises på min konto under ordreoversigt');
            $table->text('status_javascriptOnClick')->nullable()->comment('Dette felt bruges ikke længere - er nu hardcoded');
            $table->tinyInteger('usynlig')->default(0);
            $table->integer('ordering')->nullable()->default(100);
        });

         $this->runSqlFile(database_path('dbsql/san_shop_faktura_statustyper.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_faktura_statustyper');
    }
};
