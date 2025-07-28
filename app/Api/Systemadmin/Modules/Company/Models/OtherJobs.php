<?php

namespace App\Api\Systemadmin\Modules\Company\Models;

use Illuminate\Database\Eloquent\Model;

class OtherJobs extends Model
{
    protected $table = "other_jobs";
    protected $fillable = [
        'company_id',
        'payload',
        'type',  
        'status',    
    ];
}
