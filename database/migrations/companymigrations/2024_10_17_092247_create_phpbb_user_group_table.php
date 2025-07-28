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
        Schema::create('phpbb_user_group', function (Blueprint $table) {
            $table->unsignedMediumInteger('group_id')->default(0)->index('group_id');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->unsignedTinyInteger('group_leader')->default(0)->index('group_leader');
            $table->unsignedTinyInteger('user_pending')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_user_group');
    }
};
