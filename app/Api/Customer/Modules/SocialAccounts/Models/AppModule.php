<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class AppModule extends Model
{
    protected $connection = null;
    protected $table = 'tbl_appmodules';

    protected $fillable = [
        'appname',
        'appstatus',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
