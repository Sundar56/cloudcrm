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
        Schema::create('phpbb_styles_template', function (Blueprint $table) {
            $table->mediumIncrements('template_id');
            $table->string('template_name')->default('')->unique('tmplte_nm');
            $table->string('template_copyright')->default('');
            $table->string('template_path', 100)->default('');
            $table->string('bbcode_bitfield')->default('kNg=');
            $table->unsignedTinyInteger('template_storedb')->default(0);
            $table->unsignedInteger('template_inherits_id')->default(0);
            $table->string('template_inherit_path')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles_template');
    }
};
