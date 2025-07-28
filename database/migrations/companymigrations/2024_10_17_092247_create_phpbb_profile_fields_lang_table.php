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
        Schema::create('phpbb_profile_fields_lang', function (Blueprint $table) {
            $table->unsignedMediumInteger('field_id')->default(0);
            $table->unsignedMediumInteger('lang_id')->default(0);
            $table->unsignedMediumInteger('option_id')->default(0);
            $table->tinyInteger('field_type')->default(0);
            $table->string('lang_value')->default('');

            $table->primary(['field_id', 'lang_id', 'option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_profile_fields_lang');
    }
};
