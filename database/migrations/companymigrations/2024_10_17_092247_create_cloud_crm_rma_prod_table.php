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
        Schema::create('cloud_crm_rma_prod', function (Blueprint $table) {
            $table->integer('id', true)->index('id');
            $table->integer('pseudo_id')->nullable()->default(0);
            $table->integer('custId');
            $table->integer('contactId');
            $table->string('itemNo', 300);
            $table->string('itemName', 300);
            $table->string('itemSerialNo', 300);
            $table->string('invoiceNo', 150);
            $table->text('description');
            $table->integer('statusId');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0);
            $table->boolean('deleted')->default(false);

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_rma_prod');
    }
};
