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
        Schema::create('phpbb_extensions', function (Blueprint $table) {
            $table->mediumIncrements('extension_id');
            $table->unsignedMediumInteger('group_id')->default(0);
            $table->string('extension', 100)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_extensions');
    }
};
