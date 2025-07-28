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
        Schema::create('cloud_variabler', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('variabel');
            $table->string('vaerdi', 5000);
            $table->text('beskrivelse');
            $table->string('sender_email')->nullable();
            $table->string('sender_display_name')->nullable();
            $table->integer('type')->default(1)->comment('1=Global,2=Mailskabelon');
            $table->integer('hidden')->default(0);
            $table->integer('company_type')->default(1)->comment('1=Crm,2=Cms,3=Wlanshop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_variabler');
    }
};
