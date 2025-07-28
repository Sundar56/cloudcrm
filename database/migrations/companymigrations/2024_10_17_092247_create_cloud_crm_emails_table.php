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
        Schema::create('cloud_crm_emails', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('recipient_email');
            $table->text('recipient_name');
            $table->text('sender_email');
            $table->text('sender_name');
            $table->text('email_headline');
            $table->text('email_body');
            $table->text('email_attachment')->nullable();
            $table->boolean('dispatched')->default(false);
            $table->boolean('deleted')->default(false);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_crm_emails');
    }
};
