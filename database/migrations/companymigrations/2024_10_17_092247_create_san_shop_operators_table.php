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
        Schema::create('san_shop_operators', function (Blueprint $table) {
            $table->increments('id');
            $table->text('brugernavn')->nullable();
            $table->text('password')->nullable();
            $table->text('last_sign_in')->nullable();
            $table->text('last_last_sign_in')->nullable();
            $table->text('last_ip_addr')->nullable();
            $table->text('last_ip_host')->nullable();
            $table->text('all_ip_addrs')->nullable();
            $table->text('all_ip_hosts')->nullable();
            $table->text('bruger_rettigheder')->nullable();
            $table->tinyInteger('usynlig')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_operators');
    }
};
