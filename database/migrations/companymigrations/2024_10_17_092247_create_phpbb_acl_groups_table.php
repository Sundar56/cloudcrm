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
        Schema::create('phpbb_acl_groups', function (Blueprint $table) {
            $table->unsignedMediumInteger('group_id')->default(0)->index('group_id');
            $table->unsignedMediumInteger('forum_id')->default(0);
            $table->unsignedMediumInteger('auth_option_id')->default(0)->index('auth_opt_id');
            $table->unsignedMediumInteger('auth_role_id')->default(0)->index('auth_role_id');
            $table->tinyInteger('auth_setting')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_acl_groups');
    }
};
