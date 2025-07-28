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
        Schema::create('phpbb_privmsgs_rules', function (Blueprint $table) {
            $table->mediumIncrements('rule_id');
            $table->unsignedMediumInteger('user_id')->default(0)->index('user_id');
            $table->unsignedMediumInteger('rule_check')->default(0);
            $table->unsignedMediumInteger('rule_connection')->default(0);
            $table->string('rule_string')->default('');
            $table->unsignedMediumInteger('rule_user_id')->default(0);
            $table->unsignedMediumInteger('rule_group_id')->default(0);
            $table->unsignedMediumInteger('rule_action')->default(0);
            $table->integer('rule_folder_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_privmsgs_rules');
    }
};
