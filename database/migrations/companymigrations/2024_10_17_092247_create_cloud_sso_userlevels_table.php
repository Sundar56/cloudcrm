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
        Schema::create('cloud_sso_userlevels', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('userlevel')->comment('1=user, 2=supervisor');
            $table->string('describ', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_sso_userlevels');
    }
};
