<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class AppVariable extends Model
{
    protected $connection = null;
    protected $table = 'tbl_app_variabler';

    protected $fillable = [
        'tbl_appmodule_id',
        'appvariable',
        'appvalue',
        'hidden',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
