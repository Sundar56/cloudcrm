<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class MicrosoftSyncStatus extends Model
{
    protected $connection = null;
    protected $table = 'tbl_microsoft_sync_status';

    protected $fillable = [
        'cloud_sso_user_id',
        'sync_status',
        'sync_at',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
