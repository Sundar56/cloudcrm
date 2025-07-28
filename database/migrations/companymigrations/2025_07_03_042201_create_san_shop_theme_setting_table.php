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
        Schema::create('san_shop_theme_setting', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name', 100)->collation('utf8mb4_general_ci')->nullable();
            $table->text('theme_description')->collation('utf8mb4_general_ci')->nullable();
            $table->text('theme_image')->collation('utf8mb4_general_ci')->nullable();
            $table->integer('status')->nullable();
            $table->text('container_option')->collation('utf8mb4_general_ci')->nullable();
            $table->text('font_type')->collation('utf8mb4_general_ci')->nullable();
            $table->text('bg_color_option')->collation('utf8mb4_general_ci')->nullable();
            $table->text('button_bg_text_color')->collation('utf8mb4_general_ci')->nullable();
            $table->text('mega_menu_text_color')->collation('utf8mb4_general_ci')->nullable();
            $table->text('mega_menu_icon')->collation('utf8mb4_general_ci')->nullable();
            $table->text('icon_option')->collation('utf8mb4_general_ci')->nullable();
            $table->integer('is_deleted')->nullable();
            $table->string('created_at', 100)->collation('utf8mb4_general_ci')->nullable();
            $table->string('updated_at', 100)->collation('utf8mb4_general_ci')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
        });

        $this->runSqlFile(database_path('dbsql/san_shop_theme_setting.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_theme_setting');
    }
};
