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
        Schema::create('cloud_sso_users', function (Blueprint $table) {
            $table->id();
            $table->string('brugernavn', 25);
            $table->string('password', 255);
            $table->integer('userlevel')->comment('1=user, 2=supervisor');
            $table->string('lastlogin', 25);
            $table->string('siteaccess', 7);
            $table->string('navn', 60);
            $table->integer('oensker_email_ved_specifik_sag')->default(1);
            $table->string('email')->default('');
            $table->integer('usynlig')->default(0);
            $table->integer('hideuser');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_sso_users');
    }
};
