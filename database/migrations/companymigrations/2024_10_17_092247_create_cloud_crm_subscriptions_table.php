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
        Schema::create('cloud_crm_subscriptions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cr_id')->default(9999);
            $table->integer('subscription');
            $table->integer('user_id_creator');
            $table->integer('associated_id')->default(0);
            $table->integer('company_id');
            $table->integer('contact_id');
            $table->string('title', 250);
            $table->integer('period');
            $table->text('description');
            $table->integer('date_start');
            $table->integer('date_renewed');
            $table->integer('date_end')->nullable();
            $table->boolean('remind');
            $table->boolean('closed');
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
        Schema::dropIfExists('cloud_crm_subscriptions');
    }
};
