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
        Schema::create('company_databases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->index()->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->constrained()
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('db_name')->index();
            $table->string('dbuser_name')->index();
            $table->string('db_password')->index()->nullable();
            $table->string('collation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_databases');
    }
};
