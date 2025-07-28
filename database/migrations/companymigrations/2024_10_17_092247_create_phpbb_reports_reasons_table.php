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
        Schema::create('phpbb_reports_reasons', function (Blueprint $table) {
            $table->smallIncrements('reason_id');
            $table->string('reason_title')->default('');
            $table->mediumText('reason_description');
            $table->unsignedSmallInteger('reason_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_reports_reasons');
    }
};
