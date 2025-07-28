<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Module;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Api\Systemadmin\Modules\Roles\Models\RoleHasPermission;
use Illuminate\Support\Facades\Crypt;
use App\Traits\sendNotification;


class AdminRolesService
{
    use sendNotification;
    /**
     * Retrieve the list of roles from the database.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function getRoleList()
    {
        try {
            $data = Role::select('id', 'name', 'display_name')
                ->where('type', 0)->get();

            //encrypt roleId
            $data = $data->map(function ($item) {
                $item->encryptedRoleId = Crypt::encrypt($item->id);
                return $item;
            });

            if ($data->isEmpty()) {
                return [
                    'status'     => false,
                    'message'    => 'Role not found.',
                    'statusCode' => 404,
                ];
            }
            return [
                'status'     => true,
                'data'       => $data,
                'message'    => 'Role List.',
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
    /**
     * Store a newly created roles in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeRoleWithPermissions(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role' => 'required|unique:roles,name,NULL,id,type,' . 0,
            ], [
                'role.required' => 'Role required',
                'role.unique'   => 'Role already exists',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $displayName = ucfirst($request->role);
            $name        = strtolower(str_replace(' ', '', $request->role));

            if (Role::where('name', $name)->where('type', 0)->exists()) {
                return [
                    'status'     => false,
                    'message'    => 'Role already exists.',
                    'errors'     => ['role' => ['Role already exists.']],
                    'statusCode' => 422,
                ];
            }
            $role = new Role();
            $role->name         = $name;
            $role->display_name = $displayName;
            $role->guard_name   = 'web';
            $role->type         = 0;
            $role->save();

            if (isset($request->permission)) {
                $roleId      = $role->id;
                $permissions = $request->permission;


                if (!is_array($permissions)) {
                    return [
                        'status'     => false,
                        'message'    => 'Invalid Data',
                        'errors'     => ['permission' => ['Permissions must be provided as an array.']],
                        'statusCode' => 422,
                    ];
                }

                $permissionData = Permission::select('permissions.id', 'permissions.name', 'permissions.module_id')
                    ->join('modules', function ($join) {
                        $join->on('modules.id', '=', 'permissions.module_id')
                            ->where('modules.module_type', '=', 1);
                    })
                    ->get();
                $permissionArray = [];
                $permissionInfo  = [];
                $moduleInfo      = [];
                foreach ($permissionData as $pdata) {
                    $pmoduleid = $pdata['module_id'];
                    if ($pmoduleid > 0) {
                        $permissionArray[$pmoduleid][] = $pdata['id'];
                        $permissionInfo[$pmoduleid][] = [
                            "id"        => $pdata['id'],
                            "name"      => $pdata['name'],
                            "module_id" => $pdata['module_id']
                        ];
                        if (!isset($moduleInfo[$pmoduleid])) {
                            $modulesplitData        = explode('.', $pdata['name']);
                            $moduleInfo[$pmoduleid] = $modulesplitData[1];
                        }
                    }
                }

                $insertArray = [];
                foreach ($permissions as $permission) {
                    $splitData  = explode('_', $permission);
                    $moduleId   = $splitData[0];
                    $actionPart = $splitData[1];
                    $actions    = [];

                    if ($actionPart === 'all') {
                        $moduleinsertArray = $permissionArray[$moduleId] ?? [];
                        foreach ($moduleinsertArray as $mdata) {
                            $insertArray[] = [
                                'permission_id' => $mdata,
                                'role_id'       => $roleId,
                            ];
                        }
                    } else {
                        $moduleName = $moduleInfo[$moduleId] ?? '';
                        switch ($actionPart) {
                            case 'create':
                                $actions = ["crm.$moduleName.create", "crm.$moduleName.store"];
                                break;
                            case 'index':
                                $actions = ["crm.$moduleName.index", "crm.$moduleName.view"];
                                break;
                            case 'edit':
                                $actions = ["crm.$moduleName.edit", "crm.$moduleName.update"];
                                break;
                            case 'delete':
                                $actions = ["crm.$moduleName.delete"];
                                break;
                            case 'block':
                                $actions = ["crm.$moduleName.block"];
                                break;
                        }

                        foreach ($actions as $action) {
                            $key = array_search($action, array_column($permissionInfo[$moduleId] ?? [], 'name'));
                            if ($key !== false) {
                                $insertArray[] = [
                                    'permission_id' => $permissionInfo[$moduleId][$key]['id'],
                                    'role_id'       => $roleId,
                                ];
                            }
                        }
                    }
                }
                $result = array_map("unserialize", array_unique(array_map("serialize", $insertArray)));

                if (!empty($result)) {
                    $defaultPermissions = [];
                    $defaultPermissionIds = Permission::where('module_id', 0)->pluck('id');
                    foreach ($defaultPermissionIds as $pid) {
                        $defaultPermissions[] = [
                            'permission_id'   => $pid,
                            'role_id'         => $roleId
                        ];
                    }
                    RoleHasPermission::insert(array_merge($defaultPermissions, $result));
                }
            }
            $this->sendNotification('privilegeschannel', [
                'message'  => "Privileges updated for role: {$role->display_name}",
                'roleId'   => $roleId,
                'roleName' => $role->display_name,
            ]);
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Role & Privileges updated successfully.',
                'statusCode' => 201,
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
    /**
     * Delete a role from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function deleteRole($roleId)
    {
        try {
            // $roleId = crypt::decrypt($roleId);
            $role = Role::where('id', $roleId)->where('type', 0)->first();
            if (!$role) {
                return [
                    'status'     => false,
                    'message'    => 'Role not found.',
                    'statusCode' => 404,
                ];
            }
            $role->delete();
            RoleHasPermission::where('role_id', $roleId)->delete();
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Role deleted successfully.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while deleting the role.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Retrieve modules and permissions for a specific role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function getModules($roleId)
    {
        try {
            return $this->getRoledata($roleId);
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Retrieve detailed role data including permissions and accessible modules.
     *
     * @param  int  $roleId
     * @param  mixed|null  $data  Additional data to include in the response
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    private function getRoledata($roleId)
    {
        try {
            // $roleId = crypt::decrypt($roleId);
            $permissionIds = RoleHasPermission::where('role_id', $roleId)->pluck('permission_id');
            $actions = [];
            $uniques = [];

            if ($permissionIds->isNotEmpty()) {
                // $permissionArray = Permission::whereIn('id', $permissionIds)->pluck('name');
                $permissionArray = Permission::whereIn('permissions.id', $permissionIds)
                    ->join('modules', function ($join) {
                        $join->on('modules.id', '=', 'permissions.module_id')
                            ->where('modules.module_type', '=', 1);
                    })
                    ->pluck('permissions.name');
                foreach ($permissionArray as $permission) {
                    $splitData = explode('.', $permission);
                    if (count($splitData) === 3) {
                        $module = $splitData[1];
                        $actionPart = $splitData[2];
                        $uniques[$module] = $module;
                        if (in_array($actionPart, ['index', 'create', 'edit', 'delete', 'block'])) {
                            $actions[] = 'crm.' . $module . '.' . $actionPart;
                        }
                    }
                }
            }

            $role = Role::select('id', 'name', 'display_name', 'type')->where('id', $roleId)->where('type', 0)->first();

            $accessModules = Module::where('module_type', 1)
                ->where('status', 1)
                ->get();
            return [
                'status' => true,
                'data'   => [
                    'actions'        => $actions,
                    'access_modules' => $uniques,
                    'modules'        => $accessModules,
                    'role'           => $role,
                ],
                'message'    => 'Modules retrieved successfully.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while retrieving modules.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
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
    public function updateRolesAndPrivileges(Request $request)
    {
        try {
            $roleId = $request->role_id;
            // $roleId = crypt::decrypt($request->role_id);
            $role   = $request->role;

            if (!Role::find($roleId)) {
                return [
                    'status'     => false,
                    'message'    => 'Role not found',
                    'statusCode' => 404,
                ];
            }
            if (isset($role)) {
                $displayName = ucfirst($role);
                $name        = strtolower(str_replace(' ', '', $role));
                $checkRoleExists = Role::where([['type', 0], ['name', $role], ['id', '!=', $roleId]])->exists();
                if ($checkRoleExists) {
                    return [
                        'status'     => false,
                        'message'    => 'Role already exists',
                        'errors'     => ['role' => ['Role already exists']],
                        'statusCode' => 422,
                    ];
                }

                Role::where('id', $roleId)->update([
                    'name'         => $name,
                    'display_name' => $displayName,
                ]);
            }

            if (!isset($request->permission)) {
                return [
                    'status'     => true,
                    'data'       => null,
                    'message'    => 'Role updated successfully',
                    'statusCode' => 201,
                ];
            }
            $permissions = $request->permission;
            // Fetch permission data
            $permissionData = Permission::select('permissions.id', 'permissions.name', 'permissions.module_id')
                ->join('modules', function ($join) {
                    $join->on('modules.id', '=', 'permissions.module_id')
                        ->where('modules.module_type', '=', 1);
                })->get();

            $permissionArray = [];
            $permissionInfo  = [];
            $moduleInfo      = [];

            foreach ($permissionData as $pdata) {
                $pmoduleId = $pdata['module_id'];
                if ($pmoduleId > 0) {
                    $permissionArray[$pmoduleId][] = $pdata['id'];
                    $permissionInfo[$pmoduleId][] = [
                        'id'        => $pdata['id'],
                        'name'      => $pdata['name'],
                        'module_id' => $pdata['module_id']
                    ];
                    if (!isset($moduleInfo[$pmoduleId])) {
                        $modulesplitData        = explode('.', $pdata['name']);
                        $moduleInfo[$pmoduleId] = $modulesplitData[1];
                    }
                }
            }
            RoleHasPermission::where('role_id', $roleId)->delete();

            $insertArray = [];
            foreach ($permissions as $permission) {
                $splitData = explode('_', $permission);
                $moduleId = $splitData[0];
                $actionPart = $splitData[1];
                $actions = [];
                if ($actionPart === 'all') {
                    $moduleInsertArray = $permissionArray[$moduleId] ?? [];
                    if ($role !== 'superadmin') {
                        $dashboardPrivileges = ['crm.privileges.dashboard', 'crm.privileges.fetchdashboard', 'crm.privileges.storedashboard'];
                        $moduleInsertArray = array_diff($moduleInsertArray, Permission::whereIn('permissions.name', $dashboardPrivileges)
                            ->pluck('id')->toArray());
                    }
                    foreach ($moduleInsertArray as $mdata) {
                        $insertArray[] = [
                            'permission_id' => $mdata,
                            'role_id' => $roleId,
                        ];
                    }
                } else {
                    $moduleName = $moduleInfo[$moduleId] ?? '';
                    switch ($actionPart) {
                        case 'create':
                            $actions = ["crm.$moduleName.create", "crm.$moduleName.store"];
                            break;
                        case 'index':
                            $actions = ["crm.$moduleName.index", "crm.$moduleName.view"];
                            break;
                        case 'edit':
                            $actions = ["crm.$moduleName.edit", "crm.$moduleName.update"];
                            break;
                        case 'delete':
                            $actions = ["crm.$moduleName.delete"];
                            break;
                        case 'block':
                            $actions = ["crm.$moduleName.block"];
                            break;
                    }
                    foreach ($actions as $action) {
                        $key = array_search($action, array_column($permissionInfo[$moduleId] ?? [], 'name'));
                        if ($key !== false) {
                            $insertArray[] = [
                                'permission_id' => $permissionInfo[$moduleId][$key]['id'],
                                'role_id'       => $roleId,
                            ];
                        }
                    }
                }
            }

            // Remove duplicate permissions
            $result = array_map('unserialize', array_unique(array_map('serialize', $insertArray)));

            if (!empty($result)) {
                $defaultPermissions = [];
                $defaultPermissionIds = Permission::where('module_id', 0)->pluck('id');
                foreach ($defaultPermissionIds as $pid) {
                    $defaultPermissions[] = [
                        'permission_id' => $pid,
                        'role_id'       => $roleId
                    ];
                }
                RoleHasPermission::insert(array_merge($defaultPermissions, $result));
            }
            $displayName   = isset($role) ? ucfirst($role) : Role::find($roleId)->display_name;
            $permissionIds = RoleHasPermission::where('role_id', $roleId)->pluck('permission_id');
            if ($permissionIds->isNotEmpty()) {
                $permissionArray = Permission::whereIn('permissions.id', $permissionIds)
                    ->join('modules', function ($join) {
                        $join->on('modules.id', '=', 'permissions.module_id')
                            ->where('modules.module_type', '=', 1);
                    })
                    ->pluck('permissions.name');
                foreach ($permissionArray as $permission) {
                    $splitData = explode('.', $permission);
                    if (count($splitData) === 3) {
                        $module = $splitData[1];
                        $actionPart = $splitData[2];
                        $uniques[$module] = $module;
                        if (in_array($actionPart, ['index', 'create', 'edit', 'delete', 'block'])) {
                            $actions[] = 'crm.' . $module . '.' . $actionPart;
                        }
                    }
                }
            }
            $this->sendNotification('privilegeschannel', [
                'message'    => "Privileges updated for role: {$displayName}",
                'roleId'     => $roleId,
                // 'privileges' => $actions,
                'roleName'   => $displayName,
            ]);
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Role & Privileges Updated',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while updating the role and privileges.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Display a admin modules list.
     * @param  $moduleType
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminModulesList()
    {
        try {
            $modules = Module::where('module_type', 1)
                ->where('status', 1)
                ->get();
            return [
                'status' => true,
                'data'   => [
                    'modules'        => $modules,
                ],
                'message'    => 'Modules retrieved successfully.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while updating the role and privileges.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Display a privileges for role.
     *
     * @param    $roleId 
     * @return \Illuminate\Http\JsonResponse
    */
    public function rolesUpdatedPrivileges(Request $request)
    {
        try {
               $roleId        = $request->id;
               $permissionIds = RoleHasPermission::where('role_id', $roleId)->pluck('permission_id');
               $privileges    = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

                return [
                    'status'     => true,
                    'message'    => 'Privileges for Updated Role',
                    'data'       => $privileges ?? null,
                    'statusCode' => 200
                ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }  

    }
}
