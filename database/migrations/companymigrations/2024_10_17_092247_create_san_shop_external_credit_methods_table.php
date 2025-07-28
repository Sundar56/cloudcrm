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
        Schema::create('san_shop_external_credit_methods', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('name');
            $table->text('description');
            $table->integer('creditDays');
            $table->string('rate');
            $table->string('textStepTwo')->default('');
            $table->string('textStepFour')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_external_credit_methods');
    }
};
