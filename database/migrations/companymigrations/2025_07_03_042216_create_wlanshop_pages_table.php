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
            Schema::create('wlanshop_pages', function (Blueprint $table) {
                $table->id();
                $table->integer('order_page');
                $table->integer('mega_menu_id')->nullable();
                $table->integer('page_category_id')->nullable();
                $table->text('title')->collation('utf8mb4_general_ci')->nullable();
                $table->text('seo_title')->collation('utf8mb4_general_ci')->nullable();
                $table->text('keyword_description')->collation('utf8mb4_general_ci')->nullable();
                $table->text('meta_description')->collation('utf8mb4_general_ci')->nullable();
                $table->integer('menu_category')->nullable();
                $table->text('url')->collation('utf8mb4_general_ci')->nullable();
                $table->text('content')->collation('utf8mb4_general_ci')->nullable();
                $table->text('mega_menu_icon')->collation('utf8mb4_general_ci')->nullable();
                $table->text('html')->collation('utf8mb4_general_ci')->nullable();
                $table->text('css')->collation('utf8mb4_general_ci')->nullable();
                $table->integer('status')->nullable();
                $table->timestamps();
            });
        }

        $this->runSqlFile(database_path('dbsql/wlanshop_pages.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wlanshop_pages');
    }
};
