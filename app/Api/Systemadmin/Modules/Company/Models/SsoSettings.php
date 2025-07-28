<?php

namespace App\Api\Systemadmin\Modules\Company\Models;

use Illuminate\Database\Eloquent\Model;

class SsoSettings extends Model
{
    protected $table = "sso_settings_access";
    protected $fillable = [
        'company_id',
        'crm_setting',
        'cms_setting',
        'shop_setting',
        'microsoft_login',
        'google_login',  
        'footer_setting',       
    ];
}
