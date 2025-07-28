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
        Schema::create('phpbb_acl_roles_data', function (Blueprint $table) {
            $table->unsignedMediumInteger('role_id')->default(0);
            $table->unsignedMediumInteger('auth_option_id')->default(0)->index('ath_op_id');
            $table->tinyInteger('auth_setting')->default(0);

            $table->primary(['role_id', 'auth_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_acl_roles_data');
    }
};
