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
        Schema::create('san_shop_frontpage_banners', function (Blueprint $table) {
            $table->integer('ID', true);
            $table->string('name');
            $table->timestamp('created')->useCurrent();
            $table->string('link');
            $table->boolean('display')->default(true);
            $table->string('path');
            $table->integer('priority');
            $table->integer('language_id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_frontpage_banners');
    }
};
