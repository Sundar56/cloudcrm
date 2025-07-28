<?php

namespace App\Api\Customer\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Services\CustomerSettingsService;
use App\Services\CompanyPrivilegesService;

class SettingsController extends BaseController
{
    protected $companySettingService;
    protected $CompanyPrivilegesService;

    public function __construct(CustomerSettingsService $companySettingService, CompanyPrivilegesService $CompanyPrivilegesService,)
    {
        $this->companySettingService    = $companySettingService;
        $this->CompanyPrivilegesService = $CompanyPrivilegesService;
    }
    /**
     * Update SSO setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateSSOsettings(Request $request)
    {
        $response = $this->companySettingService->ssoSettingsUpdate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Show SSO setting from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function viewSSOsettings(Request $request)
    {
        $response = $this->companySettingService->ssoSettingsShow($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Store a newly created SMTP global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createSmtpGlobalSettings(Request $request)
    {
        $response = $this->companySettingService->smtpGlobalSettingsCreate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Store a newly created SMS global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createSmsGlobalSettings(Request $request)
    {
        $response = $this->companySettingService->smsGlobalSettingsCreate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Show global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function viewSmtpGlobalsettings(Request $request)
    {
        $response = $this->companySettingService->showSmtpGlobalSetting($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Show global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function viewSmsGlobalsettings(Request $request)
    {
        $response = $this->companySettingService->showSmsGlobalSetting($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Store a newly created user setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createUsersettings(Request $request)
    {
        $response = $this->companySettingService->userSettingsCreate($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Show user sso setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showUsersettings(Request $request)
    {
        $response = $this->companySettingService->showUserSetting($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Show Global setting Values.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function viewGlobalSettingValues(Request $request)
    {
        $response = $this->companySettingService->getGlobalSettingValues($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
   /**
     * Display a permission for roles customer app modules.
     *
     * @param  \Illuminate\Http\Request  $request
     *  @param  $roleId  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAccessControlPrivileges($roleId,Request $request)
    {
        $response = $this->CompanyPrivilegesService->companyPrivilegesView($roleId, $request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a modules for customer app in company.
     *
     * @param  \Illuminate\Http\Request  $request
     *  @param  $moduleType
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerAppModules(Request $request)
    {
        $response = $this->CompanyPrivilegesService->companyAppModulesList($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Test Smtp credentials for global settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConfigMail(Request $request)
    {
        $response = $this->companySettingService->testConfigMail($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }
        return $this->sendResponse($response['data'], $response['message']);
    }
}
