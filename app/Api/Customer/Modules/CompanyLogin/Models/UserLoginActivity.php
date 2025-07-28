<?php

namespace App\Api\Customer\Modules\CompanyLogin\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginActivity extends Model
{
    protected $connection = null;
    protected $table = 'tbl_user_login_activity';

    protected $fillable = [
        'userid',
        'logintime',
        'logouttime',
        'ipaddress',
        'duration',
        'useragent',
    ];

    public $timestamps = true;

    protected $dates = [
        'logintime',
        'logouttime',
        'created_at',
        'updated_at',
    ];
    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
