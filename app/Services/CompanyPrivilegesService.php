<?php

namespace App\Services;

use App\Models\RoleHasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Module;
use Illuminate\Support\Facades\Crypt;
use App\Traits\sendNotification;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;


class CompanyPrivilegesService
{
    use sendNotification;

    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }

    
    public function companyPrivilegesStore(Request $request)
    {
        try {
            $company_id = $request->companyId;
            // $company_id = crypt::decrypt($request->companyId);


            // Validate the request data
            $validator = Validator::make($request->all(), [
                'role' => 'required|unique:roles,name,NULL,id,company_id,' . $company_id,
                'moduleType' => 'required',
            ], [
                'role.required' => 'Role required',
                'role.unique' => 'Role already exists',
                'moduleType.required' => 'Module Type required',
            ]);

            if ($validator->fails()) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400,
                ];
            }

            $name = strtolower(str_replace(' ', '', $request->role));

            if (Role::where([['company_id', $company_id], ['name', $name]])->exists()) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => ["error" => ["Role Already Exist"]],
                    'statusCode' => 400,
                ];
            }
            $module_type = strtolower($request->moduleType);

            $moduleMapping = [
                'crm'      => 2,
                'cms'      => 3,
                'shop'     => 4,
                'customer' => 5,
            ];
            $module_type_id = $moduleMapping[$module_type] ?? null;

            if (is_null($module_type_id)) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => ["error" => ["Invalid module type."]],
                    'statusCode' => 400,
                ];
            }

            $role = new Role();
            $role->name = $name;
            $role->display_name = ucfirst($request->role);
            $role->guard_name = 'web';
            $role->company_id = $company_id;
            $role->type = 1;
            $role->save();

            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
            if (!$dbDetails) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("Database details not found.")],
                    'statusCode' => 422
                ];
            }
            $this->companyDatabaseService->configureDatabaseConnection($dbDetails); 
            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                return [
                    'status'     => false,
                    'message'    => 'server Error',
                    'errors'     => ["error" => array("MySQL connection failed.")],
                    'statusCode' => 500
                ];
            }

            $ssoUserlevels = DB::connection($dbDetails->db_name)->table('cloud_sso_userlevels')->insert([
                'userlevel' => $role->id,
                'describ'   => $name,
            ]);

            if (!isset($request->permission)) {
                return [
                    'status' => true,
                    'data' => null,
                    'message' => 'Role Added Successfully',
                    'statusCode' => 200,
                ];
            }

            $permissions = $request->permission;
            $permissionData = Permission::select('permissions.id', 'permissions.name', 'permissions.module_id')
                ->join('modules', 'modules.id', '=', 'permissions.module_id')
                ->where('modules.module_type', '=', $module_type_id)
                ->get();

            $permissionArray = [];
            $permissionInfo = [];
            $moduleInfo = [];

            foreach ($permissionData as $pdata) {
                $pmoduleid = $pdata['module_id'];
                if ($pmoduleid > 0) {
                    $permissionArray[$pmoduleid][] = $pdata['id'];
                    $permissionInfo[$pmoduleid][] = [
                        "id" => $pdata['id'],
                        "name" => $pdata['name'],
                        "module_id" => $pdata['module_id']
                    ];
                    if (!isset($moduleInfo[$pmoduleid])) {
                        $modulesplitData = explode('.', $pdata['name']);
                        $moduleInfo[$pmoduleid] = $modulesplitData[1];
                    }
                }
            }

            $insertArray = [];
            foreach ($permissions as $permission) {
                $splitData = explode('_', $permission);
                $moduleId = $splitData[0];
                $actionPart = $splitData[1];
                $actions = [];

                if ($actionPart === 'all') {
                    $moduleinsertArray = $permissionArray[$moduleId] ?? [];
                    $insertArray = array_merge($insertArray, $moduleinsertArray);
                } else {
                    $modulename = $moduleInfo[$moduleId] ?? '';
                    switch ($actionPart) {
                        case 'create':
                            $actions = ["$module_type.$modulename.create", "$module_type.$modulename.store"];
                            break;
                        case 'index':
                            $actions = ["$module_type.$modulename.index", "$module_type.$modulename.view"];
                            break;
                        case 'edit':
                            $actions = ["$module_type.$modulename.edit", "$module_type.$modulename.update"];
                            break;
                        case 'delete':
                            $actions = ["$module_type.$modulename.delete"];
                            break;
                        case 'block':
                            $actions = ["$module_type.$modulename.block"];
                            break;
                    }

                    foreach ($actions as $action) {
                        $key = array_search($action, array_column($permissionInfo[$moduleId] ?? [], 'name'));
                        if ($key !== false) {
                            $insertArray[] = [
                                'permission_id' => $permissionInfo[$moduleId][$key]['id'],
                                'role_id' => $role->id,
                                'module_type' => $module_type_id,
                            ];
                        }
                    }
                }
            }

            $result = array_map("unserialize", array_unique(array_map("serialize", $insertArray)));
            if (!empty($result)) {
                RoleHasPermission::insert($result);
            }

            $this->sendNotification('privilegeschannel', [
                'message'  => "Privileges updated for role: {$role->display_name}",
                'roleId'   => $role->id,
                'roleName' => $role->display_name,
            ]);

            return [
                'status' => true,
                'data'       => null,
                'message' => 'Role & Privileges Updated.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function companyPrivilegesView($roleId, Request $request)
    {
        try {
            // $roleId = crypt::decrypt($roleId);
            $module_type = strtolower($request->moduleType);
            $moduleMapping = [
                'crm'      => 2,
                'cms'      => 3,
                'shop'     => 4,
                'customer' => 5,
            ];

            $module_type_id = $moduleMapping[$module_type] ?? null;

            if (is_null($module_type_id)) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => ["error" => ["Invalid module type."]],
                    'statusCode' => 400,
                ];
            }

            $permissionIds = RoleHasPermission::where([['role_id', $roleId], ['module_type', $module_type_id]])->pluck('permission_id');
            $role = Role::find($roleId);

            $permissionArray = [];
            if ($permissionIds->isNotEmpty()) {
                foreach ($permissionIds as $id) {
                    $permissionArray[] = Permission::where('id', $id)->pluck('name')->first();
                }
            }

            $actions = [];
            $uniques = [];
            foreach ($permissionArray as $permission) {
                $splitData = explode('.', $permission);
                if (count($splitData) === 3) {
                    $module = $splitData[1];
                    $actionPart = $splitData[2];
                    $uniques[$module] = $module;

                    if ($actionPart === 'index') {
                        $actions[] = $module_type . '.' . $module . '.index';
                    }
                    if ($actionPart === 'create') {
                        $actions[] = $module_type . '.' . $module . '.create';
                    }
                    if ($actionPart === 'edit') {
                        $actions[] = $module_type . '.' . $module . '.edit';
                    }
                    if ($actionPart === 'delete') {
                        $actions[] = $module_type . '.' . $module . '.delete';
                    }
                    if ($actionPart === 'block') {
                        $actions[] = $module_type . '.' . $module . '.block';
                    }
                }
            }

            $accessModules = Module::where('module_type', $module_type_id)->where('status', 1)->get();

            return [
                'status' => true,
                'message' => 'Company Roles.',
                'data' => [
                    'permission' => $actions,
                    'access_modules' => $uniques,
                    'modules' => $accessModules,
                    'role' => $role,
                ],
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function companyPrivilegesUpdate($request)
    {
        try {
            $roleId = $request->roleId;
            // $roleId = crypt::decrypt($request->roleId);
            $permissions = $request->permission;

            if (isset($request->roleName)) {
                $company_id = $request->companyId;

                $validator = Validator::make($request->all(), [
                    'roleName' => 'required|unique:roles,name,' . $roleId . ',id,company_id,' . $company_id,
                    'moduleType' => 'required',
                ], [
                    'roleName.required' => 'Role required',
                    'roleName.unique' => 'Role already exists',
                    'moduleType.required' => 'Module Type required',
                ]);

                if ($validator->fails()) {
                    return [
                        'status' => false,
                        'message' => 'Validation Error.',
                        'errors' => $validator->errors(),
                        'statusCode' => 400,
                    ];
                }

                $name = strtolower(str_replace(' ', '', $request->roleName));
                $check_rolename_exist = Role::where([
                    ['company_id', $company_id],
                    ['name', $name],
                    ['id', '!=', $roleId]
                ])->exists();

                $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
                if (!$dbDetails) {
                    return [
                        'status'     => false,
                        'message'    => 'Validation Error',
                        'errors'     => ["error" => array("Database details not found.")],
                        'statusCode' => 422
                    ];
                }
                $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
                if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                    return [
                        'status'     => false,
                        'message'    => 'server Error',
                        'errors'     => ["error" => array("MySQL connection failed.")],
                        'statusCode' => 500
                    ];
                }

                $ssoUserlevels = DB::connection($dbDetails->db_name)
                ->table('cloud_sso_userlevels')
                ->where('userlevel', $roleId)
                ->update([
                    'describ' => $name, 
                ]);

                if ($check_rolename_exist) {
                    return [
                        'status' => false,
                        'message' => 'Validation Error.',
                        'errors' => ['The role has already been taken.'],
                        'statusCode' => 404,
                    ];
                }

                $module_type = strtolower($request->moduleType);
                $moduleMapping = [
                    'crm'      => 2,
                    'cms'      => 3,
                    'shop'     => 4,
                    'customer' => 5,
                ];

                $module_type_id = $moduleMapping[$module_type] ?? null;

                if (is_null($module_type_id)) {
                    return [
                        'status' => false,
                        'message' => 'Validation Error.',
                        'errors' => ['Invalid module type.'],
                        'statusCode' => 400,
                    ];
                }

                $displayName = ucfirst($request->roleName);
                Role::where('id', $roleId)->update([
                    'name' => $name,
                    'display_name' => $displayName,
                ]);
            }

            if (!isset($permissions)) {
                RoleHasPermission::where([['role_id', $roleId], ['module_type', $module_type_id]])->delete();
                return [
                    'status' => true,
                    'data'       => null,
                    'message' => 'Roles and Privileges Updated successfully.',
                    'statusCode' => 200,
                ];
            }

            $role = Role::where('id', $roleId)->pluck('name')->first();
            $permissionData = Permission::select('permissions.id', 'permissions.name', 'permissions.module_id')
                ->join('modules', 'modules.id', '=', 'permissions.module_id')
                ->where('modules.module_type', '=', $module_type_id)
                ->get();

            $permissionArray = [];
            $permissionInfo = [];
            $moduleInfo = [];
            foreach ($permissionData as $pdata) {
                $pmoduleid = $pdata['module_id'];
                if ($pmoduleid > 0) {
                    $permissionArray[$pmoduleid][] = $pdata['id'];
                    $permissionInfo[$pmoduleid][] = [
                        'id' => $pdata['id'],
                        'name' => $pdata['name'],
                        'module_id' => $pdata['module_id'],
                    ];
                    if (!isset($moduleInfo[$pmoduleid])) {
                        $modulesplitData = explode('.', $pdata['name']);
                        $moduleInfo[$pmoduleid] = $modulesplitData[1];
                    }
                }
            }

            RoleHasPermission::where([['role_id', $roleId], ['module_type', $module_type_id]])->delete();

            $insertArray = [];
            foreach ($permissions as $permission) {
                $splitData = explode('_', $permission);
                $moduleId = $splitData[0];
                $actionPart = $splitData[1];
                $actions = [];

                if ($actionPart === 'all') {
                    $moduleinsertArray = $permissionArray[$moduleId] ?? [];

                    if ($role !== 'superadmin') {
                        $dashboardPrivileges = ['crm.privileges.dashboard', 'crm.privileges.fetchdashboard', 'crm.privileges.storedashboard'];
                        $moduleinsertArray = array_diff($moduleinsertArray, Permission::whereIn('permissions.name', $dashboardPrivileges)
                            ->pluck('id')->toArray());
                    }

                    foreach ($moduleinsertArray as $mdata) {
                        $insertArray[] = [
                            'permission_id' => $mdata,
                            'role_id' => $roleId,
                            'module_type' => $moduleId,
                        ];
                    }
                } else {
                    $modulename = $moduleInfo[$moduleId] ?? '';
                    switch ($actionPart) {
                        case 'create':
                            $actions = [
                                "$module_type.$modulename.create",
                                "$module_type.$modulename.store",
                            ];
                            break;
                        case 'index':
                            $actions = [
                                "$module_type.$modulename.index",
                                "$module_type.$modulename.view",
                            ];
                            break;
                        case 'edit':
                            $actions = [
                                "$module_type.$modulename.edit",
                                "$module_type.$modulename.update",
                            ];
                            break;
                        case 'delete':
                            $actions = ["$module_type.$modulename.delete"];
                            break;
                        case 'block':
                            $actions = ["$module_type.$modulename.block"];
                            break;
                    }

                    foreach ($actions as $action) {
                        $key = array_search($action, array_column($permissionInfo[$moduleId] ?? [], 'name'));
                        if ($key !== false) {
                            $insertArray[] = [
                                'permission_id' => $permissionInfo[$moduleId][$key]['id'],
                                'role_id' => $roleId,
                                'module_type' => $module_type_id,
                            ];
                        }
                    }
                }
            }

            $result = array_map('unserialize', array_unique(array_map('serialize', $insertArray)));

            if (!empty($result)) {
                $defaultPermissions = [];
                RoleHasPermission::insert(array_merge($defaultPermissions, $result));
            }

            $displayName   = isset($role) ? ucfirst($role) : Role::find($roleId)->display_name;
            $permissionIds = RoleHasPermission::where('role_id', $roleId)->pluck('permission_id');
            if ($permissionIds->isNotEmpty()) {
                $permissionArray = Permission::whereIn('permissions.id', $permissionIds)
                    ->join('modules', function ($join) {
                        $join->on('modules.id', '=', 'permissions.module_id')
                            ->where('modules.module_type', '=', 5);
                    })
                    ->pluck('permissions.name');
                foreach ($permissionArray as $permission) {
                    $splitData = explode('.', $permission);
                    if (count($splitData) === 3) {
                        $module = $splitData[1];
                        $actionPart = $splitData[2];
                        $uniques[$module] = $module;
                        if (in_array($actionPart, ['index', 'create','edit', 'delete', 'block','view'])) {
                            $actions[] = 'customer.' . $module . '.' . $actionPart;
                        }
                    }        
                }
                $actions = array_unique($actions);
            }

            $this->sendNotification('privilegeschannel', [
                'message'    => "Privileges updated for role: {$displayName}",
                'roleId'     => $roleId,
                'privileges' => $actions,
                'roleName'   => $displayName,
            ]);

            return [
                'status'     => true,
                'message'    => 'Privileges Updated successfully.',
                'data'       => null,
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function CompanyModulesList(Request $request)
    {
        try {
            $module_type = strtolower($request->moduleType);
            if (empty($module_type)) {
                return [
                    'status'     => false,
                    'message'    => 'Validation error',
                    'errors'     => ['error' => array('Module Type required')],
                    'statusCode' => 400
                ];
            }

            $moduleMapping = [
                'crm'      => 2,
                'cms'      => 3,
                'shop'     => 4,
                'customer' => 5,
            ];

            $module_type_id = $moduleMapping[$module_type] ?? null;

            $modules = Module::where('module_type', $module_type_id)
                ->where('status', 1)
                ->get();
            return [
                'status'     => true,
                'message'    => 'Company Modules list',
                'data'   => [
                    'modules'        => $modules,
                ],
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function getCompanyRoles($companyId)
    {
        try {
            $roles = Role::where('company_id', $companyId)->get();

            if ($roles->isEmpty()) {
                return [
                    'status'     => false,
                    'message'    => 'No roles found for the specified company.',
                    'statusCode' => 404,
                ];
            }

            $data = $roles->map(function ($item) {
                $item->encryptedRoleId = Crypt::encrypt($item->id);
                $item->encryptedCompanyId = Crypt::encrypt($item->company_id);
                return $item;
            });
            return [
                'status'     => true,
                'data'       => $data,
                'message'    => 'Company Roles.',
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
    public function deleteCompanyRole($companyId , $roleId, Request $request)
    {
        try {
            // $roleId =  crypt::decrypt($roleId);
            $role = Role::find($roleId);
            if (!$role) {
                return [
                    'status'     => false,
                    'message'    => 'Role not found.',
                    'statusCode' => 404,
                ];
            }
            $validator = Validator::make($request->all(), [
                'moduleType' => 'required',
            ], [
                'moduleType.required' => 'Module Type required',
            ]);
            if ($validator->fails()) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400,
                ];
            }
            $role->delete();

            $module_type = strtolower($request->moduleType);
            $moduleMapping = [
                    'crm'      => 2,
                    'cms'      => 3,
                    'shop'     => 4,
                    'customer' => 5,
            ];

            $module_type_id = $moduleMapping[$module_type] ?? null;

            RoleHasPermission::where([['role_id', $roleId], ['module_type', $module_type_id]])->delete();

             $dbDetails = $this->companyDatabaseService->getDatabaseDetails($companyId);
                if (!$dbDetails) {
                    return [
                        'status'     => false,
                        'message'    => 'Validation Error',
                        'errors'     => ["error" => array("Database details not found.")],
                        'statusCode' => 422
                    ];
                }
                $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
                if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                    return [
                        'status'     => false,
                        'message'    => 'server Error',
                        'errors'     => ["error" => array("MySQL connection failed.")],
                        'statusCode' => 500
                    ];
                }

                $ssoUserlevels = DB::connection($dbDetails->db_name)
                ->table('cloud_sso_userlevels')
                ->where('userlevel', $roleId)
                ->delete();

            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Company Role Deleted successfully.',
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
    public function companyAppModulesList(Request $request)
    {
        try {
            $module_type = strtolower($request->moduleType);
            if (empty($module_type)) {
                return [
                    'status'     => false,
                    'message'    => 'Validation error',
                    'errors'     => ['error' => array('Module Type required')],
                    'statusCode' => 400
                ];
            }

            $moduleMapping = [
                'customer' => 5,
            ];
            $company_id  = $request->companyId;     
            $ssoSettings = SsoSettings::where('company_id', $company_id)->first(); 
            if (!$ssoSettings) {
                return [
                    'status'     => false,
                    'message'    => 'SSO settings not found for the provided company ID',
                    'errors'     => ["error" => array("SSO settings not found for the provided company ID")],
                    'statusCode' => 402,
                ];
            }
       
            if ($ssoSettings->crm_setting == 0) {
                Module::where('slug', 'app_crm')->update(['status' => 0]);
            }else{
                Module::where('slug', 'app_crm')->update(['status' => 1]);
            }

            if ($ssoSettings->cms_setting == 0) {
                Module::where('slug', 'app_cms')->update(['status' => 0]);
            }else{
                Module::where('slug', 'app_cms')->update(['status' => 1]);
            }

            if ($ssoSettings->shop_setting == 0) {
                Module::where('slug', 'app_webshop')->update(['status' => 0]);
            }else{
                Module::where('slug', 'app_webshop')->update(['status' => 1]);
            }

            $module_type_id = $moduleMapping[$module_type] ?? null;
            $modules  = Module::where('module_type', $module_type_id)
                ->where('status', 1)
                ->get();

            return [
                'status'     => true,
                'message'    => 'AccessControl App Modules list',
                'data'   => [
                    'modules' => $modules,
                ],
                'statusCode' => 200
            ];
          
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
