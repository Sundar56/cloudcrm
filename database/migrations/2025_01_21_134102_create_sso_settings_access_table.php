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
        Schema::create('sso_settings_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->integer('crm_setting')->default(0);
            $table->integer('cms_setting')->default(0);
            $table->integer('shop_setting')->default(0);
            $table->integer('microsoft_login')->default(0);
            $table->integer('google_login')->default(0);
            $table->integer('footer_setting')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_settings_access');
    }
};
