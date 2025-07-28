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
        Schema::create('tbl_microsoft_sync_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cloud_sso_user_id');
            $table->tinyInteger('sync_status')->default(0)->comment('0 =  sync disable, 1 = sync enable');
            $table->datetime('sync_at')->nullable();
            $table->timestamps();

            $table->foreign('cloud_sso_user_id')->references('id')->on('cloud_sso_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_microsoft_sync_status');
    }
};
