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
        Schema::create('user_otp_verifys', function (Blueprint $table) {
            $table->id();
            $table->integer('otp');
            $table->enum('verify_status', ['0', '1', '2'])
                ->default('0')
                ->comment('0 - Not Verified, 1 - Verified, 2 - Verified_time_exist');
            $table->unsignedBigInteger('user_id');
            $table->datetime('verify_otp_time')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otp_verifys');
    }
};
