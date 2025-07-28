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
        Schema::create('migration_log', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('filename', 250);
            $table->integer('migrated_at_timestamp');
        });

        $this->runSqlFile(database_path('dbsql/migration_log.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_log');
    }
};
