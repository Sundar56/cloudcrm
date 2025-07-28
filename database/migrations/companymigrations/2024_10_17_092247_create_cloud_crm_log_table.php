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
        Schema::create('cloud_crm_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('associated_id');
            $table->integer('type');
            $table->integer('sub_type');
            $table->integer('user_id');
            $table->text('content_text');
            $table->integer('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_log');
    }
};
