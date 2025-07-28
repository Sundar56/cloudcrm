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
        Schema::create('phpbb_extension_groups', function (Blueprint $table) {
            $table->mediumIncrements('group_id');
            $table->string('group_name')->default('');
            $table->tinyInteger('cat_id')->default(0);
            $table->unsignedTinyInteger('allow_group')->default(0);
            $table->unsignedTinyInteger('download_mode')->default(1);
            $table->string('upload_icon')->default('');
            $table->unsignedInteger('max_filesize')->default(0);
            $table->text('allowed_forums');
            $table->unsignedTinyInteger('allow_in_pm')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_extension_groups');
    }
};
