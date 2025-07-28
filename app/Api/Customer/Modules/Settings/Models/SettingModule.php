<?php

namespace App\Api\Customer\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SettingModule extends Model
{
    protected $connection = null;
    protected $table      = 'tbl_setting_modules';

    protected $fillable = [
        'settingname',
        'settingstatus',
        'session_timeout',
    ];

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
