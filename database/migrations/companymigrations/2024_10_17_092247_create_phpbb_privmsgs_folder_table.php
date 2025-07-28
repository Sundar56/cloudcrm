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
        Schema::create('phpbb_privmsgs_folder', function (Blueprint $table) {
            $table->mediumIncrements('folder_id');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->string('folder_name')->default('');
            $table->unsignedMediumInteger('pm_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_privmsgs_folder');
    }
};
