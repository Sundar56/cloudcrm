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
        Schema::create('san_shop_ikoner', function (Blueprint $table) {
            $table->increments('id');
            $table->text('kategori_navn')->nullable();
            $table->text('billede_dir')->nullable();
            $table->text('hierarki')->nullable();
        });

        $this->runSqlFile(database_path('dbsql/san_shop_ikoner.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_ikoner');
    }
};
