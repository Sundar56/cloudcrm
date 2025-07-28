<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Api\Systemadmin\Modules\Adminuser\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\SendEmailJob;
use App\Api\Systemadmin\Modules\Roles\Models\RoleHasPermission;
use Spatie\Permission\Models\Permission;
use App\Traits\sendNotification;


class AdminUserService
{
    use sendNotification;
    /**
     * Display a paginated list of users with optional search functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getUsersList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page'   => 'sometimes|integer|min:1',
                'length' => 'sometimes|integer|min:1|max:500',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error.',
                    'errors'     => $validator->errors(),
                    'statusCode' => 400
                ];
            }
            $columns = [
                'email',
                'users.id',
                'users.name',
                'user_phone',
                'roles.name',
                'users.created_at',
            ];
            $page = $request->input('page') ?: '1';
            $perPage = $request->input('length') ?: env("TABLE_LIST_LENGTH");

            $dbdata  = User::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('users.id', 'users.name', 'users.email', 'users.user_phone', 'roles.display_name as role_name');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $dbdata->where(function ($query) use ($search, $columns) {
                    foreach ($columns as $key => $column) {
                        if ($key == 0) {
                            $query->where($column, 'like', '%' . $search . '%');
                        } else {
                            $query->orWhere($column, 'like', '%' . $search . '%');
                        }
                    }
                });
            }

            $order = $request->input('order_column') ?: 'users.created_at';
            $orderDirection = $request->input('order_dir') ?: 'desc';
            $dbdata = $dbdata
                ->orderBy($order, $orderDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            // if ($dbdata->isEmpty()) {
            //     return [
            //         'status'     => false,
            //         'message'    => 'No data found.',
            //         'data'       => [],
            //         'statusCode' => 404
            //     ];
            // }

            $data = $dbdata->map(function ($item) {
                $item->encryptedId = Crypt::encrypt($item->id);
                return $item;
            });
            return [
                'status'  => true,
                'message' => 'User list.',
                'data'    => [
                    'list'         => $data,
                    'currentPage'  => $dbdata->currentPage(),
                    'totalPages'   => $dbdata->lastPage(),
                    'recordsTotal' => $dbdata->total()
                ],
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'Server Error.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Store a newly created user in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'       => 'required|string|max:255',
                'user_type'  => 'required|integer',
                'email'      => 'required|email|max:255|unique:users,email',
                // 'user_phone' => 'required|string|max:20',
            ], [
                'name.required'       => 'User Name required',
                'email.required'      => 'Email required',
                'email.email'         => 'Must be a valid email address',
                // 'user_phone.required' => 'Phone required',
                'user_type.required'  => 'User type is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $password     = Str::random(10);
            $hashPassword = Hash::make($password);
            $displayName  = ucfirst($request->name);
            $user = User::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => $hashPassword,
                'user_displayname' => $displayName,
                'user_phone'       => $request->user_phone,
                'is_blocked'       => $request->is_blocked == 'true' ? 1 : 0,
                'two_factor_authentication'=>  $request->input('two_factor_authentication') ?: 0,
            ]);
            $role = Role::where('id', $request->user_type)
                ->where('type', 0)
                ->first();
            if ($role) {
                $user->assignRole($role->name);
                SendEmailJob::dispatch($request->email, $password, $request->name,'Adminuser Password','system');
                return [
                    'status'     => true,
                    'message'    => 'User created successfully',
                    'data'       => null,
                    'statusCode' => 201
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Role not found or invalid',
                    'errors'     => ['error' => 'Role does not Exist'],
                    'statusCode' => 400
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Get details of a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function editUser($userId)
    {
        try {
            // $userId = crypt::decrypt($userId);
            $data = User::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('users.*', 'model_has_roles.role_id as role_id', 'roles.name as role_name')
                ->where('users.id', $userId)
                ->first();
            if (!$data) {
                return [
                    'status'     => false,
                    'message'    => 'User not found.',
                    'data'       => null,
                    'statusCode' => 404
                ];
            }
            return [
                'status'     => true,
                'message'    => 'User retrieved successfully.',
                'data'       => $data,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Update an existing user's details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'       => 'required',
                'email'      => 'required|email',
                // 'user_phone' => 'required',
            ], [
                'name.required'       => 'User Name required',
                'email.required'      => 'Email required',
                'email.email'         => 'Must be a valid email address',
                // 'user_phone.required' => 'Phone required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422,
                ];
            }

            $userId      = $request->user_id;
            $displayName = ucfirst($request->name);
        
            $updateUser  = User::where('id', $userId)->update([
                'name'             => $request->name,
                'email'            => $request->email,
                'user_phone'       => $request->user_phone,
                'is_blocked'       => $request->is_blocked == 'true' ? 1 : 0,
                'user_displayname' => $displayName,
                'two_factor_authentication'=>  $request->input('two_factor_authentication') ?: 0,
            ]);

            $roleId      = $request->role_id;
            $existRoleId = DB::table('model_has_roles')->select('role_id')->where('model_id', $userId)->first();
            $userName    = User::select('name')->where('id', $userId)->first(); 
            $roleName    = Role::select('display_name')->where('id',$roleId)->first();

           if ($roleId && $roleId != $existRoleId->role_id) {       
                $permissionIds = RoleHasPermission::where('role_id', $roleId)->pluck('permission_id');
                $privileges    = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                
                $this->sendNotification('adminuserupdatedrolechannel', [
                    'message'       => "Role updated for: {$userName->name}",
                    'userId'        => $userId,
                    'existRoleId'   => $existRoleId->role_id,
                    'updatedRoleId' => $roleId,
                    // 'permissions'   => $privileges,
                    'roleName'      => $roleName->display_name
                ]);
            }
            $updateRole = DB::table('model_has_roles')
                ->where('model_id', $userId)
                ->update(['role_id' => $roleId]);

            if ($updateUser === 0 && $updateRole === 0) {
                return [
                    'status'     => false,
                    'message'    => 'No changes were made.',
                    'statusCode' => 200,
                ];
            }
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'User updated successfully.',
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
     * Delete a user from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function deleteUser($userId)
    {
        try {
            // $userId = crypt::decrypt($userId);
            $user = User::where('users.id', $userId)->first();
            if (!$user) {
                return [
                    'status'     => false,
                    'message'    => 'User not found.',
                    'statusCode' => 404,
                ];
            }
            if($user->id == 1){
                return [
                    'status'     => false,
                    'message'    => 'User not delete.',
                    'statusCode' => 422,
                ];
            }
            $user->delete();
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'User deleted successfully.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while deleting the user.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Reset the password for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetUserPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id'       => 'required',
                'password' => 'required|min:8',
            ], [
                'id.required'       => 'User ID is required',
                'password.required' => 'Password is required',
                'password.min'      => 'Password must be at least 8 characters',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422,
                ];
            }
            $userId         = $request->id;
            // $userId    = crypt::decrypt($request->id);
            $userExists = User::find($userId);
            if (!$userExists) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not found.',
                ], 404);
            }
            $newPassword    = $request->password;
            $hashedPassword = Hash::make($newPassword);
            User::where('id', $userId)->update([
                'password' => $hashedPassword
            ]);

            SendEmailJob::dispatch($request->email, $newPassword, $request->name,'system');

            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Password reset successfully.',
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
                ->where('type', 0)->where('name', '!=', 'superadmin')->get();

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
     * Display a privileges for role.
     *
     * @param  $userId $companyId
     * @return \Illuminate\Http\JsonResponse
    */
    public function updatedAdminuserPrivileges(Request $request)
    {
        try {
               $userId        = $request->id;
               $user          = DB::table('model_has_roles')
                                ->select('role_id')->where('model_id', $userId)->first();
               $permissionIds = RoleHasPermission::where('role_id', $user->role_id)->pluck('permission_id');  
               $privileges    = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

                return [
                    'status'     => true,
                    'message'    => 'Privileges for User Updated Role',
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
