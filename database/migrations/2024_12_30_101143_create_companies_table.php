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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->index();
            $table->string('company_name')->index();
            $table->string('vat_id')->index()->nullable();
            $table->string('company_phone')->index()->nullable();
            $table->string('invoice_email')->index()->nullable();
            $table->string('ean_number')->index()->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_banner')->nullable();
            $table->text('address')->nullable();
            $table->string('zipcode')->index()->nullable();
            $table->string('city')->index()->nullable();
            $table->string('country')->index()->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('lastfile_updated_at')->nullable();
            $table->string('apikey')->nullable();
            $table->string('apisecret')->nullable();
            $table->string('domain_name')->nullable();
            $table->enum('migrate_status', ['0', '1', '2', '3', '4', '5', '6', '7'])
            ->default('0')
            ->comment('0 - default, 1 - company_database, 2 - crm_migrate, 3 - sso_migrate, 4 - shop_migrate, 5 - config , 6 - dump, 7 - completed');
            $table->tinyInteger('mail_config')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
