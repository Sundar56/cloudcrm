<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class UnicontaCustomerAddress extends Model
{
    protected $connection = null;
    protected $table = 'tbl_uniconta_delivery_address';

    protected $fillable = [
        'row_id',
        'name',
        'tbl_uniconta_customer_account',
        'reference_number',
        'email',
        'phone',
        'country',
        'address1',
        'address2',
        'city',
        'zipcode',
        'vat',
        'notes',
        'image_path',
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
