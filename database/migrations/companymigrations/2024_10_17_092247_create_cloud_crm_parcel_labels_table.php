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
        Schema::create('cloud_crm_parcel_labels', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('created_at')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('customer_id')->nullable();
            $table->text('track_and_trace');
            $table->text('firmanavn');
            $table->text('fakturaAdresse');
            $table->integer('fakturaPostnummer')->default(0);
            $table->text('fakturaBy');
            $table->text('navn');
            $table->text('telefon');
            $table->text('email');
            $table->text('reference');
            $table->boolean('shipment_saturday')->default(false);
            $table->integer('lead_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_parcel_labels');
    }
};
