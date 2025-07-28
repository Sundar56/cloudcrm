<?php

namespace App\Api\Customer\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class AppOptions extends Model
{
    protected $connection = null;
    protected $table      = 'tbl_appoptions';

    protected $fillable = [
        'appoptions_id',
        'appoptionname',
        'appoptiontype',
        'appoptionvalue',
        'appoptionrequired',
    ];

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
