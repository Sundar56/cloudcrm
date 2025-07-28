<?php

namespace App\Api\Customer\Modules\CompanyLogin\Models;

use Illuminate\Database\Eloquent\Model;

class PageModules extends Model
{
    protected $connection = null;
    protected $table = 'tbl_pagemodules';

    protected $fillable = [
        'pagename',
        'pageslug',
        'activitystatus',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
