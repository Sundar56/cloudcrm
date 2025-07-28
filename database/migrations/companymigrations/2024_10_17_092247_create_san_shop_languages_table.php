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
        Schema::create('san_shop_languages', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 200)->default('');
            $table->string('shortname', 20)->default('');
            $table->integer('vat')->nullable()->default(0);
            $table->string('urlprefix', 20)->nullable()->default('');
            $table->string('locale', 45)->nullable()->default('Danish');
            $table->string('phonenumber_prefix', 10)->nullable()->default('+45');
            $table->string('url', 200)->nullable()->default('');
            $table->string('currency', 4)->default('');
            $table->string('language_shortcode', 20)->nullable()->default('da');
            $table->integer('identical_to_language_id')->nullable();
        });

        $this->runSqlFile(database_path('dbsql/san_shop_languages.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_languages');
    }
};
