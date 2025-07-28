<?php

namespace App\Api\Systemadmin\Modules\Company\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Userimage extends Model
{
    use HasFactory;
    protected $table = "company_users_image";
    protected $fillable = [
        'company_id',
        'user_id',
        'local_imagepath',
        'main_imagepath',
        'status',
    ];
}
