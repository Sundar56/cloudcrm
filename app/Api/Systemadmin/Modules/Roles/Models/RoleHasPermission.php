<?php

namespace App\Api\Systemadmin\Modules\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleHasPermission extends Model
{
    use HasFactory;
    protected $table = "role_has_permissions";
    protected $fillable = [
        'permission_id',
        'role_id',
    ];
}
