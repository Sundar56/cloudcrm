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
      Schema::create('tbl_notes', function (Blueprint $table) {
        // $table->engine = 'InnoDB'; 
        $table->charset = 'utf8mb4';
        $table->collation = 'utf8mb4_unicode_ci';

        $table->id();
        $table->unsignedBigInteger('cloud_sso_user_id');

        $table->longText('title')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
        $table->longText('note')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

        $table->tinyInteger('favorite')->default(0)->comment('0 = Not select favorite, 1 = selected favorite');
        $table->timestamps();

        $table->foreign('cloud_sso_user_id')->references('id')->on('cloud_sso_users')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_notes');
    }
};
