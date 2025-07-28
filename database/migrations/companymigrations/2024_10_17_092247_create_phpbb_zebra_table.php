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
        Schema::create('phpbb_zebra', function (Blueprint $table) {
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedMediumInteger('zebra_id')->default(0);
            $table->unsignedTinyInteger('friend')->default(0);
            $table->unsignedTinyInteger('foe')->default(0);

            $table->primary(['user_id', 'zebra_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_zebra');
    }
};
