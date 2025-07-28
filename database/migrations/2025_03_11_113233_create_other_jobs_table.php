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
        Schema::create('other_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->json('payload')->nullable();
            $table->string('type')->nullable();
            $table->enum('status', ['0', '1', '2','3'])
            ->default('0')
            ->comment('0 - pending, 1 - running, 2 - completed, 3 - failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_jobs');
    }
};
