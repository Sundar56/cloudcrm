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
        Schema::create('san_shop_cat', function (Blueprint $table) {
            $table->increments('id');
            $table->text('gruppe')->nullable();
            $table->text('sortering1')->nullable();
            $table->text('sortering2')->nullable();
            $table->unsignedInteger('count')->nullable()->default(0);
            $table->string('gruppe_slug', 200)->nullable();
            $table->string('sortering1_slug', 200)->nullable();
            $table->string('sortering2_slug', 200)->nullable();
            $table->integer('checked')->nullable()->default(2);
        });

        $this->runSqlFile(database_path('dbsql/san_shop_cat.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_cat');
    }
};
