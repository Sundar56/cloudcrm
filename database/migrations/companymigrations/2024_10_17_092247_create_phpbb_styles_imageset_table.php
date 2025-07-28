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
        Schema::create('phpbb_styles_imageset', function (Blueprint $table) {
            $table->mediumIncrements('imageset_id');
            $table->string('imageset_name')->default('')->unique('imgset_nm');
            $table->string('imageset_copyright')->default('');
            $table->string('imageset_path', 100)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_styles_imageset');
    }
};
