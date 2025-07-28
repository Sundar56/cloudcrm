<?php

namespace App\Api\Customer\Modules\CompanyLogin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPageActivity extends Model
{
    use HasFactory;

    protected $table = 'tbl_user_page_activity';
    protected $fillable = [
        'cloud_sso_user_id	',
        'pagename',
        'module',
        'starttime',
        'endtime',
        'duration',
    ];
    public $timestamps = true;
}
