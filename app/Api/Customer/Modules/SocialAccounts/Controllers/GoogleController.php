<?php

namespace App\Api\Customer\Modules\SocialAccounts\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CompanyDatabaseService;
use App\Http\Controllers\Api\BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\GoogleService;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Customer\Modules\Employees\Models\Employees;

class GoogleController extends BaseController
{
    protected $companyDatabaseService;
    protected $googleService;
    public function __construct(CompanyDatabaseService $companyDatabaseService,GoogleService $googleService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->googleService          = $googleService;
    }
    public function googleLogin(Request $request)
    {      
        $response = $this->googleService->accessGoogleLogin($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    public function updateGoogleCredentials(Request $request)
    {
        $response = $this->googleService->updateGoogleClientId($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
     public function viewGoogleCredentials(Request $request)
    {
        $response = $this->googleService->showGoogleClientId($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


}
