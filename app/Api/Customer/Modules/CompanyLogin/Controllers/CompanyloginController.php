<?php

namespace App\Api\Customer\Modules\CompanyLogin\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;
use App\Services\CompanyImagesService;
use App\Services\CompanyloginService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\DB;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;

class CompanyloginController extends BaseController
{

    protected $guard;
    protected $companyBanner;
    protected $companyUserlogin;
    protected $companyDatabaseService;

    public function __construct(CompanyImagesService $companyBanner, CompanyloginService $companyUserlogin, CompanyDatabaseService $companyDatabaseService)
    {
        $this->guard = "api";
        $this->companyBanner = $companyBanner;
        $this->companyUserlogin = $companyUserlogin;
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Handle the company banner and logo request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyBanner(Request $request)
    {
        $response = $this->companyBanner->companyImages($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Handle the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserlogin(Request $request)
    {
        $response = $this->companyUserlogin->companyLogin($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Handle the logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function signOut(Request $request)
    // {
    //     try {
    //         $token = auth($this->guard)->getToken();

    //         $invalidate = auth($this->guard)->invalidate($token);

    //         if ($invalidate) {
    //             return $this->sendResponse([], 'Successfully logout');
    //         }
    //     } catch (\Exception $e) {
    //         return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
    //     }
    // }
    public function signOut(Request $request)
    {
        try {
            $token = auth($this->guard)->getToken();
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$userId || !$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID or User ID not found in token"]],
                    'statusCode' => 401,
                ];
            }

            $logoutTime = now();
            // $lastRecord = UserloginActivity::on($dbDetails->db_name)->where('userid',$employee->id)->latest()->first();
            $userLoginActivity = UserloginActivity::on($dbName)->where('userid', $userId)
            ->where('logouttime', null)->latest()->first();
            if ($userLoginActivity && ($userLoginActivity->logouttime === null || $userLoginActivity->logouttime === '')) {
                $userLoginActivity->logouttime = $logoutTime;
                $userLoginActivity->duration   = DB::raw("TIMESTAMPDIFF(SECOND, logintime, '$logoutTime')");
                $userLoginActivity->save();
            }
            DB::connection($dbName)->table('cloud_sso_users')
                ->where('id', $userId)
                ->update([
                    // 'lastlogin'  => $logoutTime,
                    'status'     => 1,
                    'updated_at' => $logoutTime,
                ]);

            $invalidate = auth($this->guard)->invalidate($token);

            if ($invalidate) {
                return $this->sendResponse([], 'Successfully logged out');
            }
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle the Forgot Password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $response = $this->companyUserlogin->forgotPassword($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Handle the password change request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $response = $this->companyUserlogin->changePassword($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

     /**
     * Handle the otp send.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otpSend(Request $request)
    {
        $response = $this->companyUserlogin->otpSend($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Handle the verify otp .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otpverify(request $request){
        $response = $this->companyUserlogin->otpverify($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

}
