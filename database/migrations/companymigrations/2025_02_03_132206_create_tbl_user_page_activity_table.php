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
        if (!Schema::hasTable('tbl_user_page_activity')) {
            Schema::create('tbl_user_page_activity', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cloud_sso_user_id');
                $table->unsignedBigInteger('pagemodule_id')->nullable()->comment('Customer Portal');
                $table->unsignedBigInteger('module_id')->nullable()->comment('Old Portal');
                $table->timestamp('starttime')->nullable();
                $table->timestamp('endtime')->nullable();
                $table->integer('duration')->nullable();
                $table->timestamps();
                // Foreign key constraint
                $table->foreign('cloud_sso_user_id')->references('id')->on('cloud_sso_users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_user_page_activity');
    }
};
