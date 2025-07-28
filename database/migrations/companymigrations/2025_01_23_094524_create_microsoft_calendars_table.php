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
        // Schema::create('microsoft_calendars', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('cloud_sso_user_id');
        //     $table->unsignedBigInteger('social_account_id');
        //     $table->string('microsoft_calendar_id')->unique(); // Microsoft calendar ID
        //     $table->string('name'); // Calendar name
        //     $table->string('color')->nullable(); // Calendar color
        //     $table->string('timezone')->nullable(); // Calendar timezone
        //     $table->timestamps(); // created_at and updated_at

        //     // Foreign key constraint
        //     $table->foreign('cloud_sso_user_id')->references('id')->on('cloud_sso_users')->onDelete('cascade');
        //     $table->foreign('social_account_id')->references('id')->on('social_accounts')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('microsoft_calendars');
    }
};
