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
        Schema::create('tbl_microsoft_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tbl_social_account_id');
            $table->string('microsoft_event_id');
            $table->string('title'); // Event title
            $table->text('body_content')->nullable(); // Event description
            $table->string('body_content_type')->default('HTML'); // Content type (HTML or PlainText)
            $table->boolean('isAllDay')->default(false);
            $table->boolean('isCancelled')->default(false);
            $table->boolean('isOrganizer')->default(true);
            $table->string('showAs')->nullable();
            $table->string('response_status')->nullable();
            $table->dateTime('response_time')->nullable();
            $table->string('type')->nullable();
            $table->text('webLink')->nullable();
            $table->dateTime('start_time'); // Event start time
            $table->dateTime('end_time'); // Event end time
            $table->string('timezone')->default('UTC'); // Event timezone
            $table->string('onlineMeetingUrl')->nullable(); // Event timezone
            $table->boolean('isOnlineMeeting')->default(false);
            $table->string('location_displayName')->nullable(); // Event location
            $table->string('location_locationType')->nullable(); // Attendee email address
            $table->string('organizer_name')->nullable(); // Attendee email address
            $table->string('organizer_address')->nullable(); // Attendee email address
            $table->json('attendees')->nullable();
            $table->json('locations')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('tbl_social_account_id')->references('id')->on('tbl_social_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_microsoft_calendar_events');
    }
};
