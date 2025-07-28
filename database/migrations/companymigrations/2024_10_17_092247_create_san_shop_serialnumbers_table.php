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
        Schema::create('san_shop_serialnumbers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('orderId')->nullable();
            $table->integer('productId')->nullable()->comment('on time of registration');
            $table->string('itemNumber', 100)->nullable();
            $table->string('serialnumber', 150)->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->integer('registratedByUserId')->nullable();
            $table->string('registratedByUsername', 45)->nullable();
            $table->string('batchUniqid', 45)->nullable()->comment('multiple serial numbers registered at the same time will have same uniqid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_serialnumbers');
    }
};
