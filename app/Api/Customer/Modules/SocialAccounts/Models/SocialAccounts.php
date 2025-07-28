<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialAccounts extends Model
{
     use HasFactory;
    protected $connection = null;
    protected $table = 'tbl_social_accounts';
    protected $fillable = [
        'cloud_sso_user_id	',
        'access_token',
        'refresh_token',
        'provider',
        'provider_name',
        'email',
        'token_expires_at',
    ];
    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
