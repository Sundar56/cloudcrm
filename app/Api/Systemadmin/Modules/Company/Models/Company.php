<?php

namespace App\Api\Systemadmin\Modules\Company\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Company extends Model implements JWTSubject
{
    use HasFactory, SoftDeletes;
    protected $table = "companies";
    protected $fillable = [
        'company_id',
        'company_name',
        'vat_id',
        'company_phone',
        'invoice_email',
        'zipcode',
        'city',
        'country',
        'ean_number',
        'address',
        'company_logo',
        'company_banner',
        'description',
        'lastfile_updated_at',
        'apikey',    
        'apisecret',   
        'domain_name',  
        'is_blocked',      
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Use primary key of Company (e.g., 'id')
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function company_database(){
        return $this->hasOne(CompanyDatabase::class,'company_id');
    }
}
