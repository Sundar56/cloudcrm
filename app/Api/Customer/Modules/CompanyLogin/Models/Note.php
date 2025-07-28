<?php

namespace App\Api\Customer\Modules\CompanyLogin\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $connection = null;
    protected $table = 'tbl_notes';

    protected $fillable = [
        'cloud_sso_user_id',
        'title',
        'note',
        'favorite',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
