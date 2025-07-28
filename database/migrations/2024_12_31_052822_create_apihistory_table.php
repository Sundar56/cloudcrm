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
        Schema::create('apihistory', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->text('x-forwarded-for')->nullable();
            $table->text('accept-encoding')->nullable();
            $table->text('accept')->nullable();
            $table->text('connection')->nullable();
            $table->text('x-forwarded-server')->nullable();
            $table->text('x-forwarded-host')->nullable();
            $table->text('host')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apihistory');
    }
};
