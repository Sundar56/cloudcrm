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
        Schema::create('phpbb_profile_fields', function (Blueprint $table) {
            $table->mediumIncrements('field_id');
            $table->string('field_name')->default('');
            $table->tinyInteger('field_type')->default(0)->index('fld_type');
            $table->string('field_ident', 20)->default('');
            $table->string('field_length', 20)->default('');
            $table->string('field_minlen')->default('');
            $table->string('field_maxlen')->default('');
            $table->string('field_novalue')->default('');
            $table->string('field_default_value')->default('');
            $table->string('field_validation', 20)->default('');
            $table->unsignedTinyInteger('field_required')->default(0);
            $table->unsignedTinyInteger('field_show_on_reg')->default(0);
            $table->unsignedTinyInteger('field_show_on_vt')->default(0);
            $table->unsignedTinyInteger('field_show_profile')->default(0);
            $table->unsignedTinyInteger('field_hide')->default(0);
            $table->unsignedTinyInteger('field_no_view')->default(0);
            $table->unsignedTinyInteger('field_active')->default(0);
            $table->unsignedMediumInteger('field_order')->default(0)->index('fld_ordr');
            $table->unsignedTinyInteger('field_show_novalue')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_profile_fields');
    }
};
