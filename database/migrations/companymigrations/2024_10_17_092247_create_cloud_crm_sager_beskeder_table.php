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
        Schema::create('cloud_crm_sager_beskeder', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fra_id')->default(0);
            $table->integer('sags_id')->default(0);
            $table->text('besked');
            $table->integer('tidspunkt')->default(0);
            $table->boolean('email_send')->default(false);
            $table->boolean('slettet')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_sager_beskeder');
    }
};
