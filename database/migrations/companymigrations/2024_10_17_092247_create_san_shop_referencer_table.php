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
        Schema::create('san_shop_referencer', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('mouseover_text', 150)->nullable()->default('');
            $table->string('picture_file_path', 200)->nullable()->default('');
            $table->string('link_destination', 200)->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_referencer');
    }
};
