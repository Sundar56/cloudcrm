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
        Schema::create('san_shop_online', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('IP', 30)->nullable();
            $table->text('user')->nullable();
            $table->integer('lastseen')->nullable();
            $table->text('browser')->nullable();
            $table->text('pageurl')->nullable();
            $table->text('refurl')->nullable();
            $table->text('sessionid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_online');
    }
};
