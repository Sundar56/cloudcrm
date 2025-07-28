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
        Schema::create('tbl_appoptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appmodule_id')->index();
            $table->string('appoptionname')->nullable();
            $table->string('appoptiontype')->nullable();
            $table->string('appoptionvalue')->nullable();
            $table->tinyInteger('appoptionrequired')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_appoptions');
    }
};
