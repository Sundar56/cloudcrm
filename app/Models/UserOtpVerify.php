<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOtpVerify extends Model
{
    use HasFactory;
    protected $table = 'user_otp_verifys';
    protected $fillable = ['otp','verify_status','user_id'];
}
