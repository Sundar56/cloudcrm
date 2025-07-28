<?php

namespace App\Api\Customer\Modules\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class UnicontaCustomerContact extends Model
{
    protected $connection = null;
    protected $table = 'tbl_uniconta_customer_contacts';

    protected $fillable = [
        'row_id',
        'name',
        'tbl_uniconta_customer_account',
        'reference_id',
        'email',
        'phone',
        'contact_type',
        'notes',
        'image_path',
        'is_deleted',
    ];

    public $timestamps = true;

    public function setConnection($dbName)
    {
        $this->connection = $dbName;
        return $this;
    }
}
