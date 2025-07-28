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
        Schema::create('phpbb_sitelist', function (Blueprint $table) {
            $table->mediumIncrements('site_id');
            $table->string('site_ip', 40)->default('');
            $table->string('site_hostname')->default('');
            $table->unsignedTinyInteger('ip_exclude')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_sitelist');
    }
};
