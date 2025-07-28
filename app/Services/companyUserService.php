<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Validator;
use App\Services\CompanyFileUploadService;
use App\Api\Systemadmin\Modules\Company\Models\Userimage;
use App\Api\Customer\Modules\Settings\Models\SettingModule;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;
use App\Api\Systemadmin\Modules\Roles\Models\RoleHasPermission;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;
use Spatie\Permission\Models\Permission;
use App\Traits\sendNotification;


class companyUserService
{
    use sendNotification;

    protected $companyDatabaseService;
    protected $CompanyFileUploadService;

    public function __construct(CompanyDatabaseService $companyDatabaseService, CompanyFileUploadService $CompanyFileUploadService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->CompanyFileUploadService = $CompanyFileUploadService;
    }

    /**
     * Display a paginated listing of the companies user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserList($companyId, Request $request)
    {
        try {
            // $company_id = crypt::decrypt($companyId);
            $company_id = $companyId;
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

            $columns = array(
                0 => 'navn',
                1 => 'email',
                2 => 'userlevel',
                3 => 'oensker_email_ved_specifik_sag',
                4 => 'hideuser',
                5 => 'lastlogin',
                6 => 'id',
                7 => 'siteaccess',
                8 => 'mfa',
                9 => 'status'
            );

            $order = $request->input('order_column') ?: 'id';
            $orderDirection = $request->input('order_dir') ?: 'asc';

            $currentPage = $request->input('page') ?: '1';
            $perPage = $request->input('length') ?: env("TABLE_LIST_LENGTH");

            $start = ($currentPage - 1) * $perPage;
            $sSearch = $request->input('search', '');

            // Run the query on the configured dynamic connection
            $userLogindata = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->select($columns);

            // Apply search filter if a search term is provided
            if (!empty($sSearch)) {
                $userLogindata->where(function ($q) use ($sSearch, $columns) {
                    foreach ($columns as $key => $value) {
                        if ($key == 3) {
                            $q->where($value, 'like', '%' . $sSearch . '%');
                        } else {
                            $q->orWhere($value, 'like', '%' . $sSearch . '%');
                        }
                    }
                });
            }

            // Apply sorting
            $userLogindata->orderBy($order, $orderDirection);

            // Clone the query before applying offset and limit for accurate total count
            $totalData = $userLogindata->count();
            $userLogindata = $userLogindata->offset($start)
            ->limit($perPage)
            ->get();
            $totalFiltered = $totalData;
            $data = [];
            if ($userLogindata->isNotEmpty()) {
                foreach ($userLogindata as $userLogin) {

                    // Set user type based on userlevel
                    // $userType = $userLogin->userlevel == 1 ? "user" : ($userLogin->userlevel == 2 ? "supervisor" : "");
                    $roleId = $userLogin->userlevel;
                    $userType = Role::where('id', $roleId)->select('display_name')->first();

                    // Set status based on oensker_email_ved_specifik_sag
                    $status = $userLogin->status == 0
                    ? 'Active'
                    : 'Inactive';
                    $mfa = $userLogin->mfa == 1 ? 'Enabled' : 'Disabled';
                    // Format last login timestamp

                    $formattedDate = (!empty($userLogin->lastlogin) && $userLogin->lastlogin !== 'null')
                    ? date('d M Y, H:i', strtotime($userLogin->lastlogin))
                    : '';
                    $encryptedUserId = Crypt::encrypt($userLogin->id);
                    $encryptedCompanyId = Crypt::encrypt($company_id);

                    $userloginData = [
                        'id'                => $userLogin->id ?? '',
                        'name'              => $userLogin->navn ?? '',
                        'email'             => $userLogin->email ?? '',
                        'usertype'          => $userType->display_name ?? '' ,
                        'mfa'               => $mfa ?? '',
                        'status'            => $status ?? '',
                        'lastlogin'         => $formattedDate ?? '',
                        'encrypteCompanyId' => $encryptedUserId ?? '',
                        'encrypteUserId'    => $encryptedCompanyId ?? '',
                        'CompanyId'         => $company_id ?? ''
                    ];

                    $data[] = $userloginData;
                }
            }

            $response = [
                'currentPage' => $currentPage,
                'totalPages' => ceil($totalFiltered / $perPage),
                'recordsTotal' => $totalFiltered,
                "list" => $data,
            ];
            return [
                'status'     => true,
                'message'    => 'Company user list',
                'data'       => $response,
                'statusCode' => 200
            ];
        } catch (\Throwable $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    /**
     * Store a newly created company user in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function companyuserCreate(Request $request)
    {
        try {
            // $company_id = Crypt::decrypt($request->company_id);
            $company_id  = $request->company_id;
            $company     = Company::select('id','company_logo')
                ->where('id', $company_id)
                ->first();
            $dbDetails  = $this->companyDatabaseService->getDatabaseDetails($company_id);

            if (!$dbDetails) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("Database details not found.")],
                    'statusCode' => 422
                ];
            }

            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

            // Check if the database connection is successful
            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                return [
                    'status'     => false,
                    'message'    => 'server Error.',
                    'errors'     =>  ["error" => array("MySQL connection failed.")],
                    'statusCode' => 500
                ];
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'username' => [
                    function ($attribute, $value, $fail) use ($dbDetails) {
                        $exists = DB::connection($dbDetails->db_name)
                        ->table('cloud_sso_users')->where('brugernavn', $value)->exists();
                        if ($exists) {
                            $fail('Username already exists');
                        }
                    },
                ],
                'email' => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) use ($dbDetails) {
                        $exists = DB::connection($dbDetails->db_name)
                        ->table('cloud_sso_users')->where('email', $value)->exists();
                        if ($exists) {
                            $fail('Email already exists');
                        }
                    },
                ],
            ], [
                'name.required'   => 'Name required',
                'username.unique' => 'Username Already Exist',
                'email.required'  => 'Email required',
                'email.email'     => 'Must be a valid Email',
                'email.unique'    => 'Email Already Exist',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $domainName   = $request->domain_name;
            $nameParts    = explode(' ', $request->name);
            $firstName    = $nameParts[0];
            $lastName     = isset($nameParts[1]) ? $nameParts[1] : '';
            $switchData   = $request->input('switchData', '1-1-1-1');
            $hideuser     = $request->input('block_user', 0);
            $password     =  Str::random(10);
            $hashPassword = Hash::make($password);
            $userId = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->insertGetId([
                'navn'          => $request->name,
                'brugernavn'    => $request->username,
                'password'      => $hashPassword,
                'email'         => $request->email,
                'userlevel'     => $request->user_type,
                'siteaccess'    => $switchData,
                'hideuser'      => $hideuser,
                'lastlogin'     => '',
                'usynlig'       => 0,
                'status'        => $request->status ?? 1,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'phone_work'    => $request->phone_work,
                'phone_private' => $request->phone_private,
                'title'         => $request->title,
                'mfa'           => $request->mfa ?? 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]); 
            $companyDatabaseService = app(CompanyDatabaseService::class);
            SendEmailJob::dispatch($request->email, $password, $request->username,'CompanyUser Password','customer',$company->company_logo,
                $company_id, $companyDatabaseService,$domainName);
            // $userImage = Userimage::create([
            //     'company_id'      => $company_id,
            //     'user_id'         => $userId,
            //     'local_imagepath' => $userImagePath ?? null,
            //     'main_imagepath'  => null,
            //     'status'          => '1',
            // ]);
            $this->CompanyFileUploadService->userImageFileUpload($request, $company_id, $userId, $dbDetails->db_name);
            // $this->CompanyFileUploadService->userImageFileUpload($request, $company_id, $userId);

            return [
                'status'     => true,
                'message'    => 'User created successfully',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    /**
     * Display a single company user record by ID.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserView(Request $request)
    {
        try {
            // $company_id = Crypt::decrypt($request->companyId);
            // $userId = Crypt::decrypt($request->userId);
            $company_id = $request->companyId;
            $userId     = $request->userId;
            $dbDetails  = $this->companyDatabaseService->getDatabaseDetails($company_id);

            if (!$dbDetails) {
                return [
                    'status'     => false,
                    'message'    => 'Validation error',
                    'errors'     => ['error' => 'Database details not found'],
                    'statusCode' => 404
                ];
            }

            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                return [
                    'status'     => false,
                    'message'    => 'Server Error',
                    'errors'     => ['error' => 'MySQL connection failed'],
                    'statusCode' => 500
                ];
            }

            $data = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')->where('id', $userId)->first();
            $roleId            = $data->userlevel;
            $userType          = Role::where('id', $roleId)->select('display_name')->first();
            $data->usertype    = $userType->display_name ?? '';
            $activatedAt       = UserloginActivity::on($dbDetails->db_name)->where('userid', $userId)->first();
            $data->activatedAt = $activatedAt->logintime ?? '';

            if ($data) {
                return [
                    'status'     => true,
                    'message'    => 'company User data',
                    'data'       => $data,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Validation error',
                    'errors'     => ['error' => array('Data not found.')],
                    'statusCode' => 400
                ];
            }
        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'Server error',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $userId is the encrpt 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserUpdate(Request $request)
    {
        try {
            $company_id = $request->companyId;
            $userId = $request->userId;
            // $company_id = Crypt::decrypt($request->company_id);
            // $userId = Crypt::decrypt($request->user_id);
            // $switchData = $request->input('switchData');
            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
            if (!$dbDetails) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("Database details not found.")],
                    'statusCode' => 400
                ];
            }
            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
            // Check if the database connection is successful
            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                return [
                    'status'     => false,
                    'message'    => 'Server Error',
                    'errors'     => ["error" => array("MySQL connection failed.")],
                    'statusCode' => 400
                ];
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'username' => [
                    function ($attribute, $value, $fail) use ($dbDetails, $userId) {
                        $exists = DB::connection($dbDetails->db_name)
                        ->table('cloud_sso_users')
                        ->where('brugernavn', $value)
                        ->where('id', '!=', $userId)
                        ->exists();
                        if ($exists) {
                            $fail('Username already exists');
                        }
                    },
                ],
                'email' => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) use ($dbDetails, $userId) {
                        $exists = DB::connection($dbDetails->db_name)
                        ->table('cloud_sso_users')
                        ->where('email', $value)
                        ->where('id', '!=', $userId)
                        ->exists();
                        if ($exists) {
                            $fail('Email already exists');
                        }
                    },
                ],
            ], [
                'name.required'   => 'Name required',
                'username.unique' => 'Username Already Exist',
                'email.required'  => 'Email required',
                'email.email'     => 'Must be a valid Email',
                'email.unique'    => 'Email Already Exist',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            
           $updatedRoleId = $request->user_type;
           $existRoleId   = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->select('userlevel','navn')
            ->where('id', $userId)->first();
           $roleName     = Role::select('display_name')->where('id',$updatedRoleId)->first();
            
           if ($updatedRoleId && ($updatedRoleId !== $existRoleId->userlevel)) {       
                // $permissionIds = RoleHasPermission::where('role_id', $updatedRoleId)->pluck('permission_id');              
                // $privileges    = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                $this->sendNotification('updatedrolechannel', [
                    'message'       => "Role updated for: {$existRoleId->navn}",
                    'userId'        => $userId,
                    'roleName'      => $roleName->display_name,
                    'updatedRoleId' => $updatedRoleId,
                    'cid'           => $company_id,
                    // 'permissions'   => $privileges,
                ]);
            }
            $nameParts    = explode(' ', $request->name);
            $firstName    = $nameParts[0];
            $lastName     = isset($nameParts[1]) ? $nameParts[1] : '';
            $updatedRows  = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->where('id', $userId)
            ->update([
                'navn'          => $request->name,
                'brugernavn'    => $request->username,
                'email'         => $request->email,
                'userlevel'     => $request->user_type,
                'siteaccess'    => $request->switchData,
                'hideuser'      => $request->block_user ? $request->block_user : 0,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'phone_work'    => $request->phone_work,
                'phone_private' => $request->phone_private,
                'title'         => $request->title,
                'usynlig'       => 0,
                'status'        => $request->status ?? 0,
                'mfa'           => $request->mfa ?? 0,
                    // 'lastlogin'  => now(),                    
                    // 'oensker_email_ved_specifik_sag' => $request->block_user ? $request->block_user : 0,
            ]);
            // $this->CompanyFileUploadService->userImageFileUpload($request, $company_id, $userId);
            $this->CompanyFileUploadService->userImageFileUpload($request, $company_id, $userId, $dbDetails->db_name);
            return [
                'status'     => true,
                'message'    => 'User Updated Successfully.',
                'data'       => null,
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
    /**
     * Remove the company user from storage.
     *
     * @param   $userIds delete single or multiple records
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserDelete(Request $request)
    {
        try {
            // $userIds = crypt::decrypt($request->input('userIds'));
            $userIds = $request->input('userIds');

            if (empty($userIds)) {
                return [
                    'status'     => false,
                    'message'    => 'validation error',
                    'errors'     => ['error' => 'No users selected.'],
                    'statusCode' => 404
                ];
            }

            $company_id = $request->input('companyId');
            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);

            if (!$dbDetails) {
                return [
                    'status'     => false,
                    'message'    => 'validation error',
                    'errors'     => ['error' => 'Database details not found.'],
                    'statusCode' => 404
                ];
            }

            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
            if (is_array($userIds)) {
                // Handle bulk delete for multiple users
                DB::connection($dbDetails->db_name)->table('cloud_sso_users')
                ->whereIn('id', $userIds)
                ->delete();
            } else {
                // Handle delete for a single user
                DB::connection($dbDetails->db_name)->table('cloud_sso_users')
                ->where('id', $userIds)
                ->delete();
            }
            return [
                'status'     => true,
                'message'    => 'Users deleted successfully.',
                'data'       => null,
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
    /**
     * Reset MFA for the company user from storage.
     *
     * @param   $userIds reset mfa single or multiple records
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUserResetMfa(Request $request)
    {
        try {
            // $userIds = crypt::decrypt($request->input('userIds'));
            $userIds = $request->input('userIds');       
            if (empty($userIds)) {
                return [
                    'status'     => false,
                    'message'    => 'validation error',
                    'errors'     => ['error' => 'No users selected.'],
                    'statusCode' => 404
                ];
            }
            $dbName = $request->get('dbName');

            if (is_array($userIds)) {
                // Reset MFA for Multiple Users
                $mfaStatus = SettingModule::on($dbName)->where('settingstatus', 1)->first();
               
                if ($mfaStatus !== null && $mfaStatus->settingstatus == 1) {
                    $mfAuthentication = 1;
                } else {
                    $mfAuthentication = 0;
                }
                foreach ($userIds as $userId) {
                    DB::connection($dbName)->table('cloud_sso_users')
                        ->where('id', $userId)
                        ->update([
                            'mfa' => $mfAuthentication,
                        ]);
                }
                $message = 'MFA Reset for Selected Users';
            } else {
                // Reset MFA for Single User
                $mfaStatus = SettingModule::on($dbName)->where('settingstatus', 1)->first();         
                if ($mfaStatus !== null && $mfaStatus->settingstatus == 1) {
                    $mfAuthentication = 1;
                } else {
                    $mfAuthentication = 0;
                }    
                DB::connection($dbName)->table('cloud_sso_users')
                ->where('id', $userIds)
                ->update([
                    'mfa' => $mfAuthentication,
                ]);
                $message = 'MFA Reset for That User';
            }
            return [
                'status'     => true,
                'message'    => $message,
                'data'       => null,
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
     /**
     * Reset MFA for the company user from storage.
     *
     * @param   $userId reset mfa single user
     * @return \Illuminate\Http\JsonResponse
     */
     public function UserResetMfa(Request $request)
    {
        try {
            $userIds = $request->input('userIds');
            if (empty($userIds)) {
                return [
                    'status'     => false,
                    'message'    => 'validation error',
                    'errors'     => ['error' => 'No users selected.'],
                    'statusCode' => 404
                ];
            }
            $dbName = $request->get('dbName'); 
            $mfaStatus = DB::connection($dbName)->table('cloud_sso_users')
            ->where('id', $userIds)
            ->value('mfa'); 
            $mfAuthentication = ($mfaStatus == 1) ? 0 : 1;
            DB::connection($dbName)->table('cloud_sso_users')
            ->where('id', $userIds)
            ->update([
                'mfa' => $mfAuthentication,
            ]);   
            return [
                'status'     => true,
                'message'    => 'MFA Reset for That User',
                'data'       => null,
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
    /**
     * Display a paginated listing of the companies user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeeUserList(Request $request)
    {
        try {
            $companyId = $request->get('companyId');
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $listType  = $request->get('list_type');

            $columns = array(
                0 => 'navn',
                1 => 'email',
                2 => 'userlevel',
                3 => 'hideuser',
                4 => 'lastlogin',
                5 => 'id',
                6 => 'status',
                7 => 'mfa',
            );  

            $order          = $request->input('order_column') ?: 'id';
            $orderDirection = $request->input('order_dir') ?: 'asc';
            $currentPage    = $request->input('page') ?: '1';
            $perPage        = $request->input('length') ?: env("TABLE_LIST_LENGTH");
            $start          = ($currentPage - 1) * $perPage;
            $sSearch        = $request->input('search', '');

            switch ($listType) {
                case 'total_employees':          
                    $userLogindata = DB::connection($dbName)
                    ->table('cloud_sso_users')
                    ->select($columns);
                    break;
                case 'success_login':         
                    $userIds = UserloginActivity::on($dbName)
                            ->select('userid')
                            ->pluck('userid'); 
                    $userLogindata = DB::connection($dbName)
                            ->table('cloud_sso_users as ssousers')
                            ->join('tbl_user_login_activity as userlogintable', 'ssousers.id', '=', 'userlogintable.userid')
                            ->whereIn('ssousers.id', $userIds)
                            ->select('ssousers.*', 'userlogintable.logintime', 'userlogintable.logouttime');          
                    break;
                case 'blocked_employees':
                    $userLogindata = DB::connection($dbName)
                    ->table('cloud_sso_users')
                    ->where('hideuser', 1)
                    ->select($columns);
                    break;
                case 'failed_login':          
                     $userLogindata = FailedLogin::on($dbName)
                            ->select('email','failedat');    
                    break;                        
                default:
                    $userLogindata = DB::connection($dbName)
                    ->table('cloud_sso_users')
                    ->select($columns);
                    break;
            }
            if (!empty($sSearch)) {
                if ($listType === 'failed_login') {
                    $userLogindata->where(function ($q) use ($sSearch) {
                        $q->where('email', 'like', '%' . $sSearch . '%')
                          ->orWhere('failedat', 'like', '%' . $sSearch . '%');
                    });
                }elseif ($listType === 'success_login') {                 
                    $userLogindata->where(function ($q) use ($sSearch, $columns) {
                        foreach ($columns as $key => $value) {
                            // Ensure to fully qualify the columns for `ssousers` and `userlogintable` tables
                            $columnName = $key == 5 ? 'ssousers.id' : ($key == 3 ? 'ssousers.' . $value : 'ssousers.' . $value);

                            if ($key == 3) { 
                                $q->where($columnName, 'like', '%' . $sSearch . '%');
                            } else {
                                $q->orWhere($columnName, 'like', '%' . $sSearch . '%');
                            }
                        }
                    });                 
               } else {
                    $userLogindata->where(function ($q) use ($sSearch, $columns) {
                        foreach ($columns as $key => $value) {
                            if ($key == 3) { 
                                $q->where($value, 'like', '%' . $sSearch . '%');
                            } else {
                                $q->orWhere($value, 'like', '%' . $sSearch . '%');
                            }
                        }
                    });
                }
            }
            // Apply sorting
            $userLogindata->orderBy($order, $orderDirection);
            // Clone the query before applying offset and limit for accurate total count
            $totalData     = $userLogindata->count();
            $userLogindata = $userLogindata->offset($start)
            ->limit($perPage)
            ->get();
            $totalFiltered = $totalData;
            $data          = [];
            if ($userLogindata->isNotEmpty()) {
                foreach ($userLogindata as $userLogin) {

                    if (isset($userLogin->failedat)) {  
                        $userDetails = DB::connection($dbName)
                            ->table('cloud_sso_users')
                            ->where('email', '=', $userLogin->email)
                            ->first();

                        if ($userDetails) {
                            $roleId   = $userDetails->userlevel;
                            $userType = Role::where('id', $roleId)->select('display_name')->first();
                            $status   = $userDetails->status == 0 ? 'Active' : 'Inactive';
                            $mfa      = $userDetails->mfa == 1 ? 'Enabled' : 'Disabled';

                            $formattedDate = (!empty($userDetails->lastlogin) && $userDetails->lastlogin !== 'null')
                                ? date('d M Y, H:i', strtotime($userDetails->lastlogin))
                                : '';
                            $encryptedUserId    = Crypt::encrypt($userDetails->id);
                            $encryptedCompanyId = Crypt::encrypt($companyId);

                            $userloginData = [
                                'name'              => $userDetails->navn ?? '',
                                'email'             => $userDetails->email ?? '',
                                'usertype'          => $userType->display_name ?? '',
                                'status'            => $status ?? 'Inactive',
                                'failedAt'          => $userLogin->failedat ?? '',                
                            ];
                            $data[] = $userloginData;
                        } else {
                              $userloginData = [
                                'name'              => '',
                                'email'             => $userLogin->email ?? '',
                                'status'            => 'Inactive' ?? '',
                                'usertype'          => '',
                                'failedAt'          => $userLogin->failedat ?? '',                
                            ];
                            $data[] = $userloginData;
                        }
                    } else {
                        $roleId   = $userLogin->userlevel;
                        $userType = Role::where('id', $roleId)->select('display_name')->first();
                        $status   = $userLogin->status == 0 ? 'Active' : 'Inactive';
                        $mfa      = $userLogin->mfa == 1 ? 'Enabled' : 'Disabled';

                        $formattedDate = (!empty($userLogin->lastlogin) && $userLogin->lastlogin !== 'null')
                            ? date('d M Y, H:i', strtotime($userLogin->lastlogin))
                            : '';
                        $encryptedUserId    = Crypt::encrypt($userLogin->id);
                        $encryptedCompanyId = Crypt::encrypt($companyId);

                        $userloginData = [
                            'id'                => $userLogin->id ?? '',
                            'name'              => $userLogin->navn ?? '',
                            'email'             => $userLogin->email ?? '',
                            'usertype'          => $userType->display_name ?? '',
                            'mfa'               => $mfa ?? '',
                            'status'            => $status ?? '',
                            'lastlogin'         => $formattedDate ?? '',
                            'encrypteCompanyId' => $encryptedUserId ?? '',
                            'encrypteUserId'    => $encryptedCompanyId ?? '',
                            'CompanyId'         => $companyId ?? '',
                            'logintime'         => $userLogin->logintime ?? '',
                            'logouttime'        => $userLogin->logouttime ?? '',
                        ];
                        $data[] = $userloginData;
                    }
                }
            }

            $response = [
                'currentPage'  => $currentPage,
                'totalPages'   => ceil($totalFiltered / $perPage),
                'recordsTotal' => $totalFiltered,
                "list"         => $data,
            ];
            return [
                'status'     => true,
                'message'    => 'Company user list',
                'data'       => $response,
                'statusCode' => 200
            ];
        } catch (\Throwable $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Display a privileges for role.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
    */
    public function updatedRolesPrivileges(Request $request)
    {
        try {
               $company_id = $request->companyId;
               $userId     = $request->userId;
               $dbDetails  = $this->companyDatabaseService->getDatabaseDetails($company_id);
                if (!$dbDetails) {
                    return [
                        'status'     => false,
                        'message'    => 'Validation error',
                        'errors'     => ['error' => 'Database details not found'],
                        'statusCode' => 404
                    ];
                }

                $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
                if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                    return [
                        'status'     => false,
                        'message'    => 'Server Error',
                        'errors'     => ['error' => 'MySQL connection failed'],
                        'statusCode' => 500
                    ];
                }

               $user          = DB::connection($dbDetails->db_name)->table('cloud_sso_users')->select('userlevel')->where('id', $userId)->first();
               $userROle      = $user->userlevel;
               $permissionIds = RoleHasPermission::where('role_id', $userROle)->pluck('permission_id');              
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
