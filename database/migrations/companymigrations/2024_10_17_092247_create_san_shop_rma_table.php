<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('san_shop_rma', function (Blueprint $table) {
            $table->increments('id');
            $table->text('navn')->nullable();
            $table->text('email')->nullable();
            $table->text('pwd')->nullable();
            $table->text('telefon')->nullable();
            $table->text('antal')->nullable();
            $table->text('varenummer')->nullable();
            $table->text('varebeskrivelse')->nullable();
            $table->text('fakturanummer')->nullable();
            $table->text('fakturadato')->nullable();
            $table->text('fejlbeskrivelse')->nullable();
            $table->text('emailbesked')->nullable();
            $table->unsignedInteger('rank')->nullable()->default(0);
            $table->text('dato')->nullable();
            $table->unsignedInteger('forloeb')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_rma');
    }
};
