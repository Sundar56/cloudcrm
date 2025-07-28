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
        Schema::create('dynamic_function', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('function_name')->nullable();
            $table->text('function_css')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });

        $this->runSqlFile(database_path('dbsql/dynamic_function.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_function');
    }
};
