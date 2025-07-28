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
        Schema::create('san_shop_mailingsliste', function (Blueprint $table) {
            $table->increments('id');
            $table->text('email')->nullable();
            $table->text('tilmeldnings_dato')->nullable();
            $table->text('count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_mailingsliste');
    }
};
