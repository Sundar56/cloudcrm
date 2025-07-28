<?php

namespace App\Api\Commanapi\Modules\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiHistory extends Model
{
    use HasFactory;
    protected $table = 'apihistory';
    protected $fillable = ['url','http_method','request_payload','response_payload','status_code','user_agent','error_message','x-forwarded-for','accept-encoding','accept','connection','x-forwarded-server','x-forwarded-host','host'];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];
}
