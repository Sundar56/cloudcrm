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
        Schema::create('phpbb_privmsgs_to', function (Blueprint $table) {
            $table->unsignedMediumInteger('msg_id')->default(0)->index('msg_id');
            $table->unsignedMediumInteger('user_id')->default(0);
            $table->unsignedMediumInteger('author_id')->default(0)->index('author_id');
            $table->unsignedTinyInteger('pm_deleted')->default(0);
            $table->unsignedTinyInteger('pm_new')->default(1);
            $table->unsignedTinyInteger('pm_unread')->default(1);
            $table->unsignedTinyInteger('pm_replied')->default(0);
            $table->unsignedTinyInteger('pm_marked')->default(0);
            $table->unsignedTinyInteger('pm_forwarded')->default(0);
            $table->integer('folder_id')->default(0);

            $table->index(['user_id', 'folder_id'], 'usr_flder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phpbb_privmsgs_to');
    }
};
