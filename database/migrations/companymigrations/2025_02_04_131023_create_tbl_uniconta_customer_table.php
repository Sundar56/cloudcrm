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
        if (!Schema::hasTable('tbl_uniconta_customers')) {
            Schema::create('tbl_uniconta_customers', function (Blueprint $table) {
                $table->id();
                $table->string('company_id');
                $table->string('account')->unique();
                $table->bigInteger('row_id')->unique();
                $table->string('name');
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('zipcode')->nullable();
                $table->string('country')->nullable();
                $table->string('company_reg_no')->nullable();
                $table->string('phone')->nullable();
                $table->string('user_language')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('vat_number')->nullable();
                $table->string('invoice_email')->nullable();
                $table->string('dimension1')->nullable();
                $table->string('payment')->nullable();
                $table->string('vat_zone')->nullable();
                $table->string('ean')->nullable();
                $table->string('posting_account')->nullable();
                $table->string('currency')->nullable();
                $table->string('group')->nullable();
                $table->string('price_group')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->tinyInteger('created_by')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_uniconta_customer');
    }
};
