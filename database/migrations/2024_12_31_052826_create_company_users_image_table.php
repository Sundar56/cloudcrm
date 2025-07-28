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
        Schema::create('company_users_image', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->index()->unsigned();
            $table->bigInteger('user_id')->index()->unsigned();
            $table->string('local_imagepath')->nullable();
            $table->string('main_imagepath')->nullable();
            $table->boolean('status')->default(false);         
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_users_image');
    }
};
