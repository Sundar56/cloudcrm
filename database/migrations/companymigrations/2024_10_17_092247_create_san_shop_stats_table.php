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
        Schema::create('san_shop_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->text('remote_addr')->nullable();
            $table->text('remote_host')->nullable();
            $table->text('http_refer')->nullable();
            $table->text('dato')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_stats');
    }
};
