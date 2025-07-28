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
        Schema::table('san_shop_varer', function (Blueprint $table) {
            $table->tinyInteger('entry_source')
                ->default(0)
                ->comment('entry_source  default => 0, crm => 1')
                ->after('lokation');
        });

        $this->runSqlFile(database_path('dbsql/san_shop_varer.sql'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('san_shop_varer', function (Blueprint $table) {
            if (Schema::hasColumn('san_shop_varer', 'entry_source')) {
                $table->dropColumn('entry_source');
            }
        });
    }
};
