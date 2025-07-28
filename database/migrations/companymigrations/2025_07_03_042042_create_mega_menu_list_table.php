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
        Schema::create('mega_menu_list', function (Blueprint $table) {
            $table->id();
            $table->integer('order_page');
            $table->text('menu_name')->nullable();
            $table->integer('menu_category')->nullable();
            $table->integer('is_deleted')->nullable();
            $table->timestamps();
        });

        $this->runSqlFile(database_path('dbsql/mega_menu_list.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mega_menu_list');
    }
};
