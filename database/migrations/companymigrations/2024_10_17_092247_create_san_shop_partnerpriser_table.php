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
        Schema::create('san_shop_partnerpriser', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kategori1_id')->default(0)->comment('obligatorisk');
            $table->integer('kategori2_id')->default(0)->comment('Hvis forskellig fra 0, så er der tale om en regel til 2. kategori.');
            $table->integer('prisinterval_nedre')->default(0)->comment('Nedre prisinterval');
            $table->integer('prisinterval_oevre')->default(0)->comment('Øvre prisinterval');
            $table->integer('rabatpct')->default(0);
            $table->tinyInteger('usynlig')->default(0);
            $table->string('kunde_gruppe', 2)->nullable()->default('A');
        });

        $this->runSqlFile(database_path('dbsql/san_shop_partnerpriser.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_partnerpriser');
    }
};
