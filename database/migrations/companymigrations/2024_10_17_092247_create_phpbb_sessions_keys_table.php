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
        Schema::create('phpbb_sessions_keys', function (Blueprint $table) {
            $table->char('key_id', 32)->default('');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->string('last_ip', 40)->default('');
            $table->unsignedInteger('last_login')->default(0)->index('last_login');

            $table->primary(['key_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_sessions_keys');
    }
};
