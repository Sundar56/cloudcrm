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
        Schema::create('san_shop_ordre_invoices', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('filename', 200)->default('');
            $table->dateTime('uploaded_at')->nullable();
            $table->integer('uploaded_by')->default(0);
            $table->integer('ordre_id')->default(0);
            $table->dateTime('sent_to_customer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_ordre_invoices');
    }
};
