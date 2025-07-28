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
        Schema::create('tbl_setting_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_module_id')->index()->nullable();
            $table->string('optionname')->nullable();
            $table->string('optiontype')->nullable();
            $table->string('optionvalue')->nullable();
            $table->string('label')->nullable();
            $table->tinyInteger('optionrequired')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_settings_option');
    }
};
