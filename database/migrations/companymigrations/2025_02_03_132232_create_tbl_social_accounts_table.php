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
        if (!Schema::hasTable('tbl_social_accounts')) {
            Schema::create('tbl_social_accounts', function (Blueprint $table) {
                $table->id();
                // $table->integer('user_id')->unsigned()->index();
                $table->unsignedBigInteger('cloud_sso_user_id');
                $table->text('access_token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->string('provider');
                $table->string('provider_name');
                $table->string('email')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->timestamps();

                $table->foreign('cloud_sso_user_id')->references('id')->on('cloud_sso_users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_social_accounts');
    }
};
