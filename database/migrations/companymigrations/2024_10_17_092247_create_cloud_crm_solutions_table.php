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
        Schema::create('cloud_crm_solutions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cr_id')->default(9999);
            $table->integer('solution');
            $table->text('implementation');
            $table->integer('user_id_creator');
            $table->integer('company_id');
            $table->integer('contact_id');
            $table->string('title', 250)->default('');
            $table->text('components');
            $table->text('integration');
            $table->text('description');
            $table->integer('date_start');
            $table->integer('date_end')->nullable();
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_solutions');
    }
};
