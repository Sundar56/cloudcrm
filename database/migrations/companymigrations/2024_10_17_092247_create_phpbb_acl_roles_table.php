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
        Schema::create('phpbb_acl_roles', function (Blueprint $table) {
            $table->mediumIncrements('role_id');
            $table->string('role_name')->default('');
            $table->text('role_description');
            $table->string('role_type', 10)->default('')->index('role_type');
            $table->unsignedSmallInteger('role_order')->default(0)->index('role_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_acl_roles');
    }
};
