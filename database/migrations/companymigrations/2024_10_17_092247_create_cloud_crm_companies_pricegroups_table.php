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
        Schema::create('cloud_crm_companies_pricegroups', function (Blueprint $table) {
            $table->integer('company_id');
            $table->string('manufacturer', 200)->default('');
            $table->string('price_group', 1)->default('A');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_companies_pricegroups');
    }
};
