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
        Schema::create('phpbb_sessions', function (Blueprint $table) {
            $table->char('session_id', 32)->default('')->primary();
            $table->unsignedMediumInteger('session_user_id')->default(0)->index('session_user_id');
            $table->unsignedMediumInteger('session_forum_id')->default(0)->index('session_fid');
            $table->unsignedInteger('session_last_visit')->default(0);
            $table->unsignedInteger('session_start')->default(0);
            $table->unsignedInteger('session_time')->default(0)->index('session_time');
            $table->string('session_ip', 40)->default('');
            $table->string('session_browser', 150)->default('');
            $table->string('session_forwarded_for')->default('');
            $table->string('session_page')->default('');
            $table->unsignedTinyInteger('session_viewonline')->default(1);
            $table->unsignedTinyInteger('session_autologin')->default(0);
            $table->unsignedTinyInteger('session_admin')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_sessions');
    }
};
