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
        Schema::create('san_shop_payment_status', function (Blueprint $table) {
            $table->id();
            $table->integer('gateway_status')->nullable();
            $table->string('created_at', 20)->collation('utf8mb4_general_ci')->nullable();
            $table->string('updated_at', 20)->collation('utf8mb4_general_ci')->nullable();
        });

       $this->runSqlFile(database_path('dbsql/san_shop_payment_status.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_payment_status');
    }
};
