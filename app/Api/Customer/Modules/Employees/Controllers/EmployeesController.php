<?php

namespace App\Api\Customer\Modules\Employees\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\companyUserService;
use App\Services\CompanyPrivilegesService;


class EmployeesController extends BaseController
{
    protected $companyEmployees;
    protected $CompanyPrivilegesService;

    public function __construct(companyUserService $companyEmployees,CompanyPrivilegesService $CompanyPrivilegesService)
    {
        $this->companyEmployees = $companyEmployees;
        $this->CompanyPrivilegesService = $CompanyPrivilegesService;
    }
    /**
     * Display a paginated listing of the companies user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeesList(Request $request)
    {
        // $companyId = $request->get('companyId');
        // $response = $this->companyEmployees->companyUserList($companyId, $request);
        $response = $this->companyEmployees->employeeUserList($request);

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
    public function storeEmployees(Request $request)
    {
        $response = $this->companyEmployees->companyuserCreate($request);
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
    public function showEmployees(Request $request)
    {
        $response = $this->companyEmployees->companyUserView($request);
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
    public function updateEmployees(Request $request)
    {
        $response = $this->companyEmployees->companyUserUpdate($request);
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
    public function deleteEmployees(Request $request)
    {
        $response = $this->companyEmployees->companyUserDelete($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Reset MFA for the company user from storage.
     *
     * @param   $userIds reset mfa single or multiple records
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeesResetMfa(Request $request)
    {
        $response = $this->companyEmployees->companyUserResetMfa($request);
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a  roles for company users.
     * @param  $companyId 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyRoles(Request $request)
    {
        $companyId = $request->get('companyId');
        $response = $this->CompanyPrivilegesService->getCompanyRoles($companyId);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
      /**
     * Reset MFA for the company user from storage.
     *
     * @param   $userId reset mfa single user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetMfa(Request $request)
    {
        $response = $this->companyEmployees->UserResetMfa($request);
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
    public function employeeRoleUpdate(Request $request)
    {
        $response = $this->companyEmployees->updatedRolesPrivileges($request);
        
        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
}
