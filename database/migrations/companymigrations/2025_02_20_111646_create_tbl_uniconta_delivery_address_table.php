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
        Schema::create('tbl_uniconta_delivery_address', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('row_id')->unique();
            $table->string('tbl_uniconta_customer_account');
            $table->string('reference_number');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('vat')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_uniconta_delivery_address');
    }
};
