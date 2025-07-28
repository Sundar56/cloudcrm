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
        Schema::create('cloud_crm_rma', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pseudo_id')->nullable()->default(0);
            $table->tinyInteger('solution_type')->default(0);
            $table->text('solution_description');
            $table->string('service_id', 17);
            $table->integer('service_type')->default(0);
            $table->integer('created_at')->default(0);
            $table->tinyText('contact_name');
            $table->integer('contact_phone')->default(0);
            $table->text('contact_email');
            $table->text('error_message');
            $table->integer('status')->default(0);
            $table->text('company_name');
            $table->text('company_address');
            $table->text('company_city');
            $table->integer('company_zip')->default(0);
            $table->boolean('deleted')->default(false);
            $table->integer('opened')->nullable();
            $table->text('attached_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_rma');
    }
};
