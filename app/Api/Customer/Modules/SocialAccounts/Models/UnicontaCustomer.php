<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class UnicontaCustomer extends Model
{
    protected $connection = null;
    protected $table = 'tbl_uniconta_customers';

    protected $fillable = [
        'company_id',
        'account',
        'row_id',
        'name',
        'address',
        'city',
        'zipcode',
        'country',
        'company_reg_no',
        'phone',
        'user_language',
        'contact_email',
        'vat_number',
        'invoice_email',
        'dimension1',
        'payment',
        'vat_zone',
        'ean',
        'posting_account',
        'currency',
        'group',
        'price_group',
        'is_deleted',
        'created_by',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
