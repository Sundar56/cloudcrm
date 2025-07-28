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
        Schema::create('san_shop_events', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 100)->nullable()->default('');
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->text('description')->nullable();
            $table->text('description_short')->nullable();
            $table->string('picture_url_1', 200)->nullable()->default('');
            $table->string('picture_url_2', 200)->nullable()->default('');
            $table->string('picture_url_3', 200)->nullable()->default('');
            $table->boolean('show_on_frontpage')->nullable()->default(true);
            $table->boolean('active')->nullable()->default(false);
        });

        $this->runSqlFile(database_path('dbsql/san_shop_events.sql'));       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_events');
    }
};
