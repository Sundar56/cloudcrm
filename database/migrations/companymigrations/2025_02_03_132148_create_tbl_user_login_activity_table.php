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
        if (!Schema::hasTable('tbl_user_login_activity')) {
            Schema::create('tbl_user_login_activity', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('userid')->index();
                $table->timestamp('logintime')->nullable();
                $table->timestamp('logouttime')->nullable();
                $table->string('ipaddress', 45)->nullable();
                $table->integer('duration')->nullable()->comment('seconds');
                $table->string('useragent')->nullable();
                $table->timestamps();
                $table->foreign('userid')->references('id')->on('cloud_sso_users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_user_login_activity');
    }
};
