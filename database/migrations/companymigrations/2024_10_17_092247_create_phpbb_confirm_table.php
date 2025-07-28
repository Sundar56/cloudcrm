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
        Schema::create('phpbb_confirm', function (Blueprint $table) {
            $table->char('confirm_id', 32)->default('');
            $table->char('session_id', 32)->default('');
            $table->tinyInteger('confirm_type')->default(0)->index('confirm_type');
            $table->string('code', 8)->default('');
            $table->unsignedInteger('seed')->default(0);
            $table->unsignedMediumInteger('attempts')->default(0);

            $table->primary(['session_id', 'confirm_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_confirm');
    }
};
