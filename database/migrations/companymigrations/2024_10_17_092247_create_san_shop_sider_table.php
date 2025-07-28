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
        Schema::create('san_shop_sider', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier', 55)->nullable();
            $table->string('titel', 70)->nullable();
            $table->text('indhold')->nullable();
            $table->unsignedInteger('count')->nullable()->default(0);
            $table->string('search_title', 200)->nullable()->default('');
            $table->string('search_meta_desc', 250)->nullable()->default('');
            $table->string('search_meta_tags', 250)->nullable()->default('');
            $table->string('contact_text', 150)->nullable()->default('');
            $table->string('belongs_to', 55)->nullable()->default('');
            $table->integer('language_id')->nullable()->default(0);
        });

        $this->runSqlFile(database_path('dbsql/san_shop_sider.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_sider');
    }
};
