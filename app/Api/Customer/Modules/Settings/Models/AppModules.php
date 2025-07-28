<?php

namespace App\Api\Customer\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class AppModules extends Model
{
    protected $connection = null;
    protected $table      = 'tbl_appmodules';

    protected $fillable = [
        'appname',
        'appstatus',
    ];

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
