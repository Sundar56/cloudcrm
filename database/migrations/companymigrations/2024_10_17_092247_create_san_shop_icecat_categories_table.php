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
        Schema::create('san_shop_icecat_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('categoryfeaturegroup_id')->nullable()->default(0);
            $table->text('navn_da')->nullable();
            $table->unsignedTinyInteger('opdateret')->nullable()->default(1);
        });

         $this->runSqlFile(database_path('dbsql/san_shop_icecat_categories.sql'));  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_icecat_categories');
    }
};
