<?php

namespace App\Api\Customer\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SettingOption extends Model
{
    protected $connection = null;
    protected $table      = 'tbl_setting_options';

    protected $fillable = [
        'setting_module_id',
        'optionname',
        'optiontype',
        'optionvalue',
        'optionrequired',
        'label',
    ];

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
