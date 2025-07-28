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
        Schema::create('phpbb_attachments', function (Blueprint $table) {
            $table->mediumIncrements('attach_id');
            $table->unsignedMediumInteger('post_msg_id')->default(0)->index('post_msg_id');
            $table->unsignedMediumInteger('topic_id')->default(0)->index('topic_id');
            $table->unsignedTinyInteger('in_message')->default(0);
            $table->unsignedMediumInteger('poster_id')->default(0)->index('poster_id');
            $table->unsignedTinyInteger('is_orphan')->default(1)->index('is_orphan');
            $table->string('physical_filename')->default('');
            $table->string('real_filename')->default('');
            $table->unsignedMediumInteger('download_count')->default(0);
            $table->text('attach_comment');
            $table->string('extension', 100)->default('');
            $table->string('mimetype', 100)->default('');
            $table->unsignedInteger('filesize')->default(0);
            $table->unsignedInteger('filetime')->default(0)->index('filetime');
            $table->unsignedTinyInteger('thumbnail')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_attachments');
    }
};
