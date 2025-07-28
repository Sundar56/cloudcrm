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
        Schema::create('phpbb_acl_options', function (Blueprint $table) {
            $table->mediumIncrements('auth_option_id');
            $table->string('auth_option', 50)->default('')->unique('auth_option');
            $table->unsignedTinyInteger('is_global')->default(0);
            $table->unsignedTinyInteger('is_local')->default(0);
            $table->unsignedTinyInteger('founder_only')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_acl_options');
    }
};
