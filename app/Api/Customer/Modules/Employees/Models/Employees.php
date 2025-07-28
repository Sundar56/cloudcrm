<?php

namespace App\Api\Customer\Modules\Employees\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;

class Employees extends Model implements JWTSubject
{
    use HasFactory;

    protected $connection = 'mysql'; 
    protected $table = 'cloud_sso_users';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'id',
        'navn',
        'email',
        'password',
        'userlevel',
        'lastlogin',
        'siteaccess',
        'oensker_email_ved_specifik_sag',
        'usynlig',
        'hideuser',
        'phone_work',
        'phone_private',
        'title',
        'user_image',
        'first_name',
        'last_name',
        'status',
        'mfa',
    ];

    public $timestamps = true;

  // The JWT Identifier (id is typically the unique identifier)
  public function getJWTIdentifier()
  {
      return $this->id;  // 'id' is the unique identifier for this model
  }

  // Custom Claims for JWT (add any additional claims you want)
  public function getJWTCustomClaims()
  {
      // For example, we can add the user's name and email as custom claims
      return [
          'name' => $this->navn,
          'email' => $this->email,
      ];
  }

  public function setConnection($dbName)
  {
      $this->connection = $dbName;
      return $this;
  }
}
