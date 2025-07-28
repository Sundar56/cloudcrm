<?php

namespace App\Api\Systemadmin\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Services\AdminRolesService;


class RolesController extends BaseController
{
    protected $rolesService;

    public function __construct(AdminRolesService $rolesService)
    {
        $this->rolesService = $rolesService;
    }
    /**
     * Retrieve the list of roles from the database.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function index()
    {
        $response = $this->rolesService->getRoleList();

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Store a newly created roles in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $response = $this->rolesService->storeRoleWithPermissions($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Delete a role from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function delete(Request $request)
    {
        $response = $this->rolesService->deleteRole($request->id);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Retrieve modules and permissions for a specific role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function getModulesByRole(Request $request)
    {
        $response = $this->rolesService->getModules($request->roleId);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Update the role and its privileges.
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function update(Request $request)
    {
        $response = $this->rolesService->updateRolesAndPrivileges($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a admin modules list.
     *  @param  $moduleType
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminModulesList()
    {
        $response = $this->rolesService->adminModulesList();

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
    /**
     * Display a admin modules list.
     *  @param  
     * @return \Illuminate\Http\JsonResponse
     */
    public function rolesUpdatedPrivilges(Request $request)
    {
        $response = $this->rolesService->rolesUpdatedPrivileges($request);

        if (!$response['status']) {
            return $this->sendError($response['message'], $response['errors'] ?? [], $response['statusCode']);
        }

        return $this->sendResponse($response['data'], $response['message']);
    }
}
