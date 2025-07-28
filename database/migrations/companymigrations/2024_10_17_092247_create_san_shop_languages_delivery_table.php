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
        Schema::create('san_shop_languages_delivery', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('language_id');
            $table->string('shortname', 200)->default('')->comment('E.g. postdanmark');
            $table->integer('active')->default(1);
        });

        $this->runSqlFile(database_path('dbsql/san_shop_languages_delivery.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_languages_delivery');
    }
};
