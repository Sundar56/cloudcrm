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
        Schema::create('recent_view_list', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable(); 
            $table->integer('product_id')->nullable(); 
            $table->string('created_at', 10)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_view_list');
    }
};
