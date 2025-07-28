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
        Schema::create('page_category', function (Blueprint $table) {
            $table->id();
            $table->text('category_name')->collation('utf8mb4_general_ci');
            $table->integer('is_deleted')->nullable();
            $table->timestamps();
        });
        $this->runSqlFile(database_path('dbsql/page_category.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_category');
    }
};
