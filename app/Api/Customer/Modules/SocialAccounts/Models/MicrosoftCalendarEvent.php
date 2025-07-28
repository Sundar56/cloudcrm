<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class MicrosoftCalendarEvent extends Model
{
    protected $connection = null;
    protected $table = 'tbl_microsoft_calendar_events';

    protected $fillable = [
        'tbl_social_account_id',
        'microsoft_event_id',
        'title',
        'body_content',
        'body_content_type',
        'isAllDay',
        'isCancelled',
        'isOrganizer',
        'showAs',
        'response_status',
        'response_time',
        'type',
        'webLink',
        'start_time',
        'end_time',
        'timezone',
        'onlineMeetingUrl',
        'isOnlineMeeting',
        'location_displayName',
        'location_locationType',
        'organizer_name',
        'organizer_address',
        'attendees',
        'locations',
        'is_deleted',
        'created_by',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
