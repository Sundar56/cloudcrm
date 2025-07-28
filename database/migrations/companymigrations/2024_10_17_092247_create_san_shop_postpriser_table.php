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
        if (!Schema::hasTable('wlanshop_pages')) {
            Schema::create('san_shop_postpriser', function (Blueprint $table) {
                $table->increments('id');
                $table->string('transporttype', 100)->nullable();
                $table->text('navn')->nullable();
                $table->text('vaegt')->nullable();
                $table->text('type')->nullable();
                $table->text('sendetype')->nullable();
                $table->text('pris')->nullable();
                $table->text('efterkrav')->nullable();
                $table->text('vaegt_start')->nullable();
                $table->text('vaegt_slut')->nullable();
                $table->integer('language_id')->nullable()->default(1);
            });
        }

        $this->runSqlFile(database_path('dbsql/san_shop_postpriser.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_postpriser');
    }
};
