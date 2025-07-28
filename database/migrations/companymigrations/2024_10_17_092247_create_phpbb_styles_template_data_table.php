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
        Schema::create('phpbb_styles_template_data', function (Blueprint $table) {
            $table->unsignedMediumInteger('template_id')->default(0)->index('tid');
            $table->string('template_filename', 100)->default('')->index('tfn');
            $table->text('template_included');
            $table->unsignedInteger('template_mtime')->default(0);
            $table->mediumText('template_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles_template_data');
    }
};
