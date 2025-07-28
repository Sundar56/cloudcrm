<?php

namespace App\Api\Systemadmin\Modules\Company\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use App\Services\CompanyFileUploadService;
use App\Services\CompanyService;
use App\Services\companyUserService;
use App\Services\CompanyPrivilegesService;
use App\Services\CompanyGeneralSettingsService;
use App\Services\CompanySsoSettingservice;

class CompanyController extends BaseController
{
    protected $companyDatabaseService;
    protected $CompanyFileUploadService;
    protected $companyService;
    protected $companyUserService;
    protected $CompanyPrivilegesService;
    protected $CompanyGeneralSettings;
    protected $CompanySsoSettingservice;

    public function __construct(CompanyDatabaseService $companyDatabaseService, CompanyFileUploadService $CompanyFileUploadService, companyService $companyService, companyUserService $companyUserService, CompanyGeneralSettingsService $CompanyGeneralSettings, CompanyPrivilegesService $CompanyPrivilegesService,CompanySsoSettingservice $CompanySsoSettingservice)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->CompanyFileUploadService = $CompanyFileUploadService;
        $this->companyService = $companyService;
        $this->companyUserService = $companyUserService;
        $this->CompanyGeneralSettings = $CompanyGeneralSettings;
        $this->CompanyPrivilegesService = $CompanyPrivilegesService;
        $this->CompanySsoSettingservice = $CompanySsoSettingservice;
    }

    /**
     * Display a paginated listing of the companies.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $response = $this->companyService->companyList($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Store a newly created company in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $response = $this->companyService->companyCreate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a  company Details.
     *
     * @param    $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $response = $this->companyService->companyView($id);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Remove the company.
     *
     * @param   $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $response = $this->companyService->companyDelete($id);
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
    public function update($id, Request $request)
    {
        $response = $this->companyService->companyUpdate($id, $request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


    /**
     * Display a paginated listing of the companies user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyuserIndex($companyId, Request $request)
    {
        $response = $this->companyUserService->companyUserList($companyId, $request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Store a newly created company user in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function companyuserStore(Request $request)
    {

        $response = $this->companyUserService->companyuserCreate($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a single company user record by ID.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyuserShow(Request $request)
    {
        $response = $this->companyUserService->companyUserView($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $userId is the encrpt 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyuserUpdate(Request $request)
    {

        $response = $this->companyUserService->companyUserUpdate($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Remove the company user from storage.
     *
     * @param   $userIds delete single or multiple records
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyuserdestroy(Request $request)
    {

        $response = $this->companyUserService->companyUserDelete($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a paginated listing of the companies general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companygeneralIndex($companyId, Request $request)
    {
        $response = $this->CompanyGeneralSettings->companyGeneralList($companyId, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a single company General settings record by ID.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function companygeneralShow(Request $request)
    {
        $response = $this->CompanyGeneralSettings->companyGeneralView($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Update the general srttings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $userId $companyId is the encrpt 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companygeneralUpdate(Request $request)
    {
        $response = $this->CompanyGeneralSettings->companyGeneralUpdate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Remove the company general settings.
     *
     * @param   $companyId  $generalSettingId delete single  records
     * @return \Illuminate\Http\JsonResponse
     */
    public function companygeneralDestroy(Request $request)
    {
        $response = $this->CompanyGeneralSettings->companyGeneralDelete($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a  roles for company.
     *  @param  $companyId 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyRoles($companyId)
    {
        $response = $this->CompanyPrivilegesService->getCompanyRoles($companyId);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Store a newly created roles and permission.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @param    $role $companyId 
     * @param   Array $permission
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function companyPrivilegesStore(Request $request)
    {
        $response = $this->CompanyPrivilegesService->companyPrivilegesStore($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a permission for roles in company.
     *
     * @param  \Illuminate\Http\Request  $request
     *  @param  $roleId  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyPrivilegesShow($roleId, Request $request)
    {
        $response = $this->CompanyPrivilegesService->companyPrivilegesView($roleId, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Remove the company Role.
     *
     * @param   $roleId  delete single  records
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyCompanyRoles($companyId, $roleId, Request $request)
    {
        $response = $this->CompanyPrivilegesService->deleteCompanyRole($companyId, $roleId, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }


    /**
     * Update the company Roles and privileges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $roleId $companyId $roleName
     * @param   Array  $permission 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyPrivilegesUpdate(Request $request)
    {
        $response = $this->CompanyPrivilegesService->companyPrivilegesUpdate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a modules for crm,cms and shop in company.
     *
     * @param  \Illuminate\Http\Request  $request
     *  @param  $moduleType
     * @return \Illuminate\Http\JsonResponse
     */
    public function CompanyModules(Request $request)
    {

        $response = $this->CompanyPrivilegesService->CompanyModulesList($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }

    /**
     * Display a latest company data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyLatestData()
    {

        $response = $this->companyService->companyLatestData();

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
     /**
     * Store a newly created company user in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SsoSettingAccess(Request $request)
    {
        $response = $this->CompanySsoSettingservice->companySsoSetting($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);  
    }
    /**
     * Display company SSO setting data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showSsoSetttingAccess($companyId)
    {
        $response = $this->CompanySsoSettingservice->companySsosettingView($companyId);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);  
    }
     /**
     * Display company SSO login setting data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showLoginSettings($companyId)
    {
        $response = $this->CompanySsoSettingservice->showLoginCredentials($companyId);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);  
    }
    /**
     * Display a single company user record by ID.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function userRoleUpdate(Request $request)
    {
        $response = $this->companyUserService->updatedRolesPrivileges($request);
        
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a modules for crm,cms and shop in company.
     *
     * @param  \Illuminate\Http\Request  $request
     *  @param  $moduleType
     * @return \Illuminate\Http\JsonResponse
     */
    public function accessControlAppsList(Request $request)
    {

        $response = $this->CompanyPrivilegesService->companyAppModulesList($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display footer in customer portal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function customerFooterSetting(Request $request)
    {
        $response = $this->CompanySsoSettingservice->customerFooterSetting($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);  
    }

}
