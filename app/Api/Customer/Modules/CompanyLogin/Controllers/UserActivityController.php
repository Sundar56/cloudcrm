<?php

namespace App\Api\Customer\Modules\CompanyLogin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompanyUserActivityService;
use App\Http\Controllers\Api\BaseController;

class UserActivityController extends BaseController
{
    protected $CompanyUserActivityService;

    public function __construct(CompanyUserActivityService $CompanyUserActivityService)
    {
        $this->CompanyUserActivityService = $CompanyUserActivityService;
    }
     /**
     * Store user page activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function pageActivityStore(Request $request)
    {
        $response = $this->CompanyUserActivityService->userPageActivity($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);   
    }
    /**
     * Store user login activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function userActivityStore(Request $request)
    {
        $response = $this->CompanyUserActivityService->userLoginActivity($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);   
    }
}
