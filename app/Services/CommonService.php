<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonService
{
    public function getSocialAccountData($dbName, $userId)
    {
        $socialAccounts = DB::connection($dbName)
            ->table('tbl_social_accounts')->where([['cloud_sso_user_id', $userId],['provider','uniconta']])->first();
        return $socialAccounts;
    }

}