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
        Schema::create('san_shop_translations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('language_id')->default(0);
            $table->string('key', 150)->nullable()->default('');
            $table->string('group', 150)->nullable()->default('');
            $table->text('value')->nullable();
            $table->boolean('is_inline')->nullable()->default(false);

            $table->index(['key', 'group'], 'key_group');
        });

        $this->runSqlFile(database_path('dbsql/san_shop_translations.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_translations');
    }
};
