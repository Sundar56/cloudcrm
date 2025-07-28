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
        Schema::create('cloud_crm_aktiviteter_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('activity_id')->index('activity_id');
            $table->integer('user_id')->index('user_id');
            $table->boolean('deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_aktiviteter_users');
    }
};
