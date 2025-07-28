<?php

namespace App\Api\Customer\Modules\CompanyLogin\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    protected $connection = null;
    protected $table = 'tbl_failed_logins';

    protected $fillable = [
        'email',
        'password',
        'error_details',
        'failedat',
    ];

    public $timestamps = true;

    protected $casts = [
        'error_details' => 'array',
    ];
    
    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
