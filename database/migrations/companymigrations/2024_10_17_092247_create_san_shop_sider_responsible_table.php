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
        Schema::create('san_shop_sider_responsible', function (Blueprint $table) {
            $table->integer('responsible_id', true);
            $table->string('identifier', 55)->nullable()->default('');
            $table->string('subject', 100)->nullable()->default('');
            $table->text('content')->nullable();
            $table->string('picture_url', 200)->nullable()->default('');
            $table->integer('language_id')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_shop_sider_responsible');
    }
};
