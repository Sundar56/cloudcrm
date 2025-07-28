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
        Schema::create('san_shop_employees', function (Blueprint $table) {
            $table->integer('employee_id', true);
            $table->string('name', 200)->nullable()->default('');
            $table->string('title', 100)->nullable()->default('');
            $table->string('description', 250)->nullable()->default('');
            $table->string('direct_phone', 45)->nullable()->default('');
            $table->string('email', 150)->nullable()->default('');
            $table->string('picture_url', 200)->nullable()->default('');
            $table->integer('position')->nullable()->default(100);
            $table->integer('language_id')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_employees');
    }
};
