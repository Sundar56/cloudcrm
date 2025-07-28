<?php

namespace App\Api\Customer\Modules\Company\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Services\CompanyService;

class CompanyeditController extends BaseController
{
    protected $companyService;
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }
       /**
     * Display a  company Details.
     *
     * @param    $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $company_id = $request->get('companyId');
        $response = $this->companyService->companyView($company_id);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Update the company details in companies storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $id is the encrpt 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $company_id = $request->get('companyId');
        $response = $this->companyService->companyUpdate($company_id, $request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
}
