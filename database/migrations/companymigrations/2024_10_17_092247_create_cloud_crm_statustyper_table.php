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
        Schema::create('cloud_crm_statustyper', function (Blueprint $table) {
            $table->integer('statusTypeID', true);
            $table->tinyText('statusTypeDescription');
            $table->integer('statusTypeCaseSort');
            $table->integer('order')->default(100);
            $table->string('progressText', 100)->default('');
            $table->string('progressColor', 45)->default('');
            $table->integer('progressPercentage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_statustyper');
    }
};
