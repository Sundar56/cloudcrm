<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class AppOption extends Model
{
    protected $connection = null;
    protected $table = 'tbl_appoptions';

    protected $fillable = [
        'appmodule_id',
        'appoptionname',
        'appoptiontype',
        'appoptionvalue',
        'appoptionrequired',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
