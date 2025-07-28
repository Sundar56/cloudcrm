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
        Schema::create('san_shop_currencyrates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('currency');
            $table->decimal('rate', 20, 6);
            $table->integer('updated_at');
        });

         $this->runSqlFile(database_path('dbsql/san_shop_currencyrates.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_currencyrates');
    }
};
