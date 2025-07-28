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
        Schema::create('tbl_setting_modules', function (Blueprint $table) {
            $table->id();
            $table->string('settingname')->nullable();
            $table->tinyInteger('settingstatus')->default(0);
            $table->string('session_timeout')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_settings_module');
    }
};
