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
        Schema::create('phpbb_login_attempts', function (Blueprint $table) {
            $table->string('attempt_ip', 40)->default('');
            $table->string('attempt_browser', 150)->default('');
            $table->string('attempt_forwarded_for')->default('');
            $table->unsignedInteger('attempt_time')->default(0)->index('att_time');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->string('username')->default('0');
            $table->string('username_clean')->default('0');

            $table->index(['attempt_forwarded_for', 'attempt_time'], 'att_for');
            $table->index(['attempt_ip', 'attempt_time'], 'att_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_login_attempts');
    }
};
