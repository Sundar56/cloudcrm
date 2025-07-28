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
        Schema::create('tbl_app_variabler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tbl_appmodule_id')->index();
            $table->string('appvariable')->nullable();
            $table->text('appvalue')->nullable();
            $table->tinyInteger('hidden')->default(0);
            $table->timestamps();

            $table->foreign('tbl_appmodule_id')->references('id')->on('tbl_appmodules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_app_variabler');
    }
};
