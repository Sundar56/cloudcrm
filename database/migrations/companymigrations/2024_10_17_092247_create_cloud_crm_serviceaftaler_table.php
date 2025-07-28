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
        Schema::create('cloud_crm_serviceaftaler', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cr_id');
            $table->string('service_id', 30);
            $table->integer('solution_id')->default(0);
            $table->string('title', 250);
            $table->integer('date_start');
            $table->integer('date_end')->default(0);
            $table->tinyInteger('service_type');
            $table->tinyInteger('response_time');
            $table->integer('customer_id');
            $table->integer('contact_id');
            $table->text('service_components');
            $table->boolean('deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_serviceaftaler');
    }
};
