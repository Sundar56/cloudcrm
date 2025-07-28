<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use App\Api\Customer\Modules\CompanyLogin\Models\PageModules;

class CompanyUserActivityService
{

    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }

    /**
     * Store a newly created company user in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */

    public function userPageActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'starttime' => 'required|date',
            'endtime'   => 'nullable|date|after_or_equal:starttime',
        ], [
            'starttime.required' => 'The starttime field is required.',
            'starttime.date'     => 'The starttime must be a valid date.',
            'endtime.date'       => 'The endtime must be a valid date.',
            'endtime.after_or_equal' => 'The endtime must be equal to or after the starttime.',
        ]);

        if ($validator->fails()) {
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => $validator->errors(),
                'statusCode' => 400
            ];
        }
        $dbName = $request->get('dbName');
        $userId = $request->get('userId');

        // Calculate duration if endtime is provided
        $duration = null;
        if ($request->starttime && $request->endtime) {
            $duration = strtotime($request->endtime) - strtotime($request->starttime);
        }

        // Create the activity record
        $activity = DB::connection($dbName)->table('tbl_user_page_activity')->insert([
            'cloud_sso_user_id' => $userId,
            'pagemodule_id'     => $request->pagemodule_id,
            'module_id'         => $request->module_id ?? null,
            'starttime'         => $request->starttime,
            'endtime'           => $request->endtime,
            'duration'          => $duration,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return [
            'status'     => true,
            'message'    => 'User page activity recorded successfully.',
            'data'       => null,
            'statusCode' => 200
        ];
    }
    public function userLoginActivity(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$companyId || !$userId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID or User ID not found in token"]],
                    'statusCode' => 401,
                ];
            }

            $loginTime = now();
            DB::connection($dbName)->table('tbl_user_login_activity')->insert([
                'userid'     => $userId,
                'logintime'  => $loginTime,
                'ipaddress'  => $request->ipAddress,
                'useragent'  => $request->userAgent,
                'created_at' => $loginTime,
                'updated_at' => $loginTime,
            ]);

            return [
                'status'     => true,
                'message'    => 'User login activity stored successfully.',
                'data'       => null,
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
