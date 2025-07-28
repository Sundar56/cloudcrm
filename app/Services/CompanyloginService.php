<?php

namespace App\Services;

use App\Services\Config;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Validator;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Customer\Modules\Employees\Models\Employees;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;
use App\Api\Customer\Modules\Settings\Models\SettingModule;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Jobs\SendVerifyOtpMailJob;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\DataSecurityService;

class CompanyloginService
{
    protected $companyDatabaseService;
    protected $DataSecurityService;

    public function __construct(CompanyDatabaseService $companyDatabaseService, DataSecurityService $DataSecurityService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->DataSecurityService =  $DataSecurityService;
    }
    /**
     * Handle the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'domain_name'  => 'required',
                'username'     => 'required',
                'password'     => 'required',
            ], [
                'domain_name.required'  => 'Domain Name is required',
                'username.required'     => 'Username is required',
                'password.required'     => 'Password is required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $domainName = $request->domain_name;
            $company    = Company::select('id', 'company_name')
                ->where('domain_name', $domainName)
                ->first();

            if (!$company) {           
                return [
                    'status'     => false,
                    'message'    => 'Company not found.',
                    'errors'     => ["error" => array("Company details not found.")],
                    'statusCode' => 404,
                ];
            }
            $companyId = $company->id;
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
                $errorData = [
                    'status'     => false,
                    'message'    => 'server Error.',
                    'errors'     =>  ["error" => array("MySQL connection failed.")],
                    'statusCode' => 500
                ];
                $failedData = new FailedLogin([
                    'email'         => $request->username,
                    'password'      => $request->password,
                    'error_details' => json_encode($errorData),
                    'failedat'      => now(),
                ]);
                $failedData->setConnection($dbDetails->db_name)->save();
                return [
                    'status'     => false,
                    'message'    => 'server Error.',
                    'errors'     =>  ["error" => array("MySQL connection failed.")],
                    'statusCode' => 500
                ];
            }

            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'brugernavn';
            $userLogin = DB::connection($dbDetails->db_name)
                ->table('cloud_sso_users')
                ->select('id', 'navn', 'email', 'password', 'brugernavn', 'mfa','hideuser','user_image','userlevel')
                ->where($fieldType, $request->username)->first();

            if($userLogin->hideuser == 1){
                $errorData = [
                    'status'     => false,
                    'message'    => 'User Blocked, Please contact your admin',
                    'errors'     =>  ["error" => array("User Blocked, Please contact your admin")],
                    'statusCode' => 400
                ];
                $failedData = new FailedLogin([
                    'email'         => $request->username,
                    // 'password'   => $request->password,
                    'error_details' => json_encode($errorData),
                    'failedat'      => now(),
                ]);
                $failedData->setConnection($dbDetails->db_name)->save();
                return [
                    'status'     => false,
                    'message'    => 'User Blocked, Please contact your admin',
                    'errors'     =>  ["error" => array("User Blocked, Please contact your admin")],
                    'statusCode' => 400
                ];
            }

            if (!$userLogin || !Hash::check($request->password, $userLogin->password)) {

                $errorData = [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => array("The username or password is incorrect")],
                    'statusCode' => 400
                ];
                $failedData = new FailedLogin([
                    'email'         => $request->username,
                    'password'      => $request->password,
                    'error_details' => json_encode($errorData),
                    'failedat'      => now(),
                ]);
                $failedData->setConnection($dbDetails->db_name)->save();
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => array("The username or password is incorrect")],
                    'statusCode' => 400
                ];
            }

            $employee        = new Employees();
            $employee->id    = $userLogin->id;
            $employee->navn  = $userLogin->navn;
            $employee->email = $userLogin->email;
            $employee->brugernavn = $userLogin->brugernavn;

            // Create JWT token for the employee
            $customClaims = [
                'id'        => $employee->id,
                'email'     => $employee->email,
                'name'      => $employee->navn,
                'username'  => $employee->brugernavn,
                'iss'       => 'CustomerPortal',
                'iat'       => (int) now()->timestamp,
                'companyId' => $companyId,
            ];

            $mfa = 0;
            $checkMfaExisting  =  DB::connection($dbDetails->db_name)->table('tbl_setting_modules')
                ->where('settingstatus', 1)->first();
            if ($checkMfaExisting) {
                if ($userLogin->mfa == 1) {
                    $mfa = 1;
                }
            }
            $settingModule = SettingModule::on($dbDetails->db_name)->where('settingstatus', 1)->first();
            $sessionTimeout = $settingModule?$settingModule->session_timeout : 1;
            $token = JWTAuth::claims($customClaims)->fromUser($employee);

            // $roles  = DB::table('model_has_roles')->where('model_id', $userLogin->userlevel)->first();
            $roleId = $userLogin->userlevel;
            // dd($roleId);
            $roleName = Role::select('display_name')->where('id', $roleId)->first();
            // dd($roleName);
            $permissionData = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id', $roleId)
                ->where('role_has_permissions.module_type', 5)
                ->pluck('permissions.name')
                ->toArray();
            // dd($permissionData);

            $userData = [
                'email'          => $employee->email,
                'userId'         => $employee->id,
                'username'       => $employee->brugernavn,
                'companyName'    => $company->company_name,
                'companyId'      => $companyId,
                'token'          => $token,
                'mfa'            => $mfa,
                'sessionTimeOut' => $sessionTimeout,
                'expire_in'      => config('jwt.ttl') * 60,
                'userImage'      => $userLogin->user_image,
                'role_name'      => $roleName->display_name,
                'roleId'         => $roleId,
                'permission'     => $permissionData,
            ];

            $lastRecord = UserloginActivity::on($dbDetails->db_name)->where('userid',$employee->id)->latest()->first();
        
            if($lastRecord && !$lastRecord->logouttime){
                $tokenExpiry = config('jwt.ttl');
                $loginTime   = Carbon::parse($lastRecord->logintime);
                $logoutTime  = $loginTime->addMinutes($tokenExpiry);

                $lastRecord->logouttime = $logoutTime;
                $lastRecord->duration   = DB::raw("TIMESTAMPDIFF(SECOND, logintime, '$logoutTime')");
                $lastRecord->save();
            }

            $loginTime  = now();
            $userSetting = new UserloginActivity([
                'userid'    => $employee->id,
                'logintime' => $loginTime,
                'ipaddress' => $request->ip(),
                'useragent' => $request->userAgent(),
            ]);
            $userSetting->setConnection($dbDetails->db_name)->save();

            DB::connection($dbDetails->db_name)->table('cloud_sso_users')
                ->where('id', $employee->id)
                ->update([
                    'status'    => 0,
                    'lastlogin' => $loginTime,
                ]);
            return [
                'status'     => true,
                'data'       => $this->DataSecurityService->encrypt($userData),
                // 'data'       => $userData,
                'message'    => 'Company user login successfully',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            $errorData = [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];

            $failedData = new FailedLogin([
                'email'         => $request->username,
                'password'      => $request->password,
                'error_details' => json_encode($errorData),
                'failedat'      => now(),
            ]);
            $failedData->setConnection($dbDetails->db_name)->save();

            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    /**
     * Handle the Forgot Password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'domain_name'  => 'required',
                'username'     => 'required',
            ], [
                'domain_name.required'  => 'Domain Name is required',
                'username.required'     => 'Username is required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $domainName = $request->domain_name;
            $company    = Company::select('id','company_logo')
                ->where('domain_name', $domainName)
                ->first();

            if (!$company) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found.',
                    'errors'     => ["error" => array("Company details not found.")],
                    'statusCode' => 404,
                ];
            }
            $companyId = $company->id;
            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($companyId);
            // dd($dbDetails);
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
                    'message'    => 'server Error.',
                    'errors'     =>  ["error" => array("MySQL connection failed.")],
                    'statusCode' => 500
                ];
            }

            $emailValidator = Validator::make($request->all(), [
                'username' => [
                    function ($attribute, $value, $fail) use ($dbDetails) {
                        $exists = DB::connection($dbDetails->db_name)
                            ->table('cloud_sso_users')->where('email', $value)->exists();
                        if (!$exists) {
                            $fail('User Not Found');
                        }
                    },
                ],
            ]);

            if ($emailValidator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $emailValidator->errors(),
                    'statusCode' => 422
                ];
            }
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'brugernavn';
            $userLogin = DB::connection($dbDetails->db_name)
                ->table('cloud_sso_users')
                ->select('id', 'brugernavn', 'email')
                ->where($fieldType, $request->username)
                ->first();
            $newPassword  = Str::random(10);
            $hashPassword = Hash::make($newPassword);
            DB::connection($dbDetails->db_name)
                ->table('cloud_sso_users')
                ->where($fieldType, $request->username)
                ->update(['password' => $hashPassword]);
                
            $companyDatabaseService = app(CompanyDatabaseService::class);
            SendEmailJob::dispatch($userLogin->email, $newPassword, $userLogin->brugernavn,'Customer Portal Forgotpassword','customer',$company->company_logo,
                $companyId, $companyDatabaseService,$domainName);

            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Password Reset Email Sent',
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
     * Handle the password change request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required',
                'confirm_password' => ['same:new_password'],
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised.',
                    'errors'     =>  ["error" => array("Company ID not found in token.")],
                    'statusCode' => 401
                ];
            }

            $oldPassword = $request->old_password;
            $newPassword = $request->new_password;
            $user = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('id', $userId)
                ->first();
            if (!Hash::check($request->old_password, $user->password)) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("Old Password Doesnot match")],
                    'statusCode' => 400
                ];
            }

            if (strcmp($oldPassword, $newPassword) == 0) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("New Password cannot be same as your current password")],
                    'statusCode' => 400
                ];
            }
            $hashPassword = Hash::make($newPassword);
            DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('id', $userId)
                ->update(['password' => $hashPassword]);

            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Password changed successfully',
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

    public function otpSend(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'domain_name' => 'required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            // $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $domainName = $request->domain_name;
            $company = Company::select('id','company_logo')
                ->where('domain_name', $domainName)
                ->first();

            if (!$company) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found.',
                    'errors'     => ["error" => array("Company not found.")],
                    'statusCode' => 404,
                ];
            }
            $companyId =  $company->id;
            $connection = $this->companyDatabaseService->connect($companyId);

            if (!$connection['status']) {
                return [
                    'status'     => false,
                    'message'    => $connection['message'],
                    'errors'     => $connection['errors'],
                    'statusCode' => $connection['statusCode']
                ];
            }

            $dbname = $connection['dbName'];
            $user = DB::connection($dbname)
                ->table('cloud_sso_users')
                ->where('email', $request->username)->first();
            // $userId = $request->attributes->get('decoded_token')->get('id');
            // $user = User::where('id', $userId)->first();

            if (!$user) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ['error' => array('user not found')],
                    'statusCode' => 422
                ];
            }
            $userId = Crypt::encrypt($user->id);
            $otp    = random_int(1000, 9999);
            $companyDatabaseService = app(CompanyDatabaseService::class);
            SendVerifyOtpMailJob::dispatch($user->email, $otp, $user->id, $user->navn,'CompanyUser Otp Verification','customer',$company->company_logo,
                $companyId, $companyDatabaseService);
            $created_at = Carbon::now()->format('Y-m-d H:i:s');
            $verify_otp_time = Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s');


            $user = DB::connection($dbname)
                ->table('tbl_otp_verifys')->insert([
                    'otp' => $otp,
                    'cloud_sso_user_id' => $user->id,
                    'verify_otp_time' => $verify_otp_time,
                    'created_at' => $created_at,
                ]);
            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'OTP send to user mail successfully!',
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
     * Handle the verify otp .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otpverify(request $request)
    {
        try {
            $first = $request->first;
            $second = $request->second;
            $third = $request->third;
            $fourth = $request->fourth;
            $domainName = $request->domain_name;

            $company = Company::select('id')
                ->where('domain_name', $domainName)
                ->first();

            if (!$company) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found.',
                    'errors'     => ["error" => array("Company not found.")],
                    'statusCode' => 404,
                ];
            }
            $companyId =  $company->id;
            $connection = $this->companyDatabaseService->connect($companyId);

            if (!$connection['status']) {
                return [
                    'status'     => false,
                    'message'    => $connection['message'],
                    'errors'     => $connection['errors'],
                    'statusCode' => $connection['statusCode']
                ];
            }

            $dbname = $connection['dbName'];
            $user = DB::connection($dbname)
                ->table('cloud_sso_users')
                ->where('email', $request->username)->first();

            $string_otp  = (int)$first . $second . $third . $fourth;
            $otp = (int)$string_otp;

            $latestOtp = DB::connection($dbname)
                ->table('tbl_otp_verifys')->where('cloud_sso_user_id', $user->id)
                ->orderBy('created_at', 'desc') // Sort by the latest record
                ->first();

            if ($latestOtp) {
                // Get the last inserted OTP
                $lastOtp = $latestOtp->otp;
                $user = DB::connection($dbname)->table('cloud_sso_users')->where('id', $user->id)->orderBy('created_at', 'desc')->first();
                if ($lastOtp === $otp) {

                    // Given date (you can retrieve this from your database or request)
                    $givenDate = strtotime(trim($latestOtp->verify_otp_time));

                    // Get the current time
                    $currentTime = strtotime(Carbon::now()->format('Y-m-d H:i:s'));

                    $latestOtp = DB::connection($dbname)
                        ->table('tbl_otp_verifys')
                        ->where('cloud_sso_user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($currentTime > $givenDate) {
                        // $latestOtp = DB::connection($dbname)
                        //     ->table('tbl_otp_verifys')->where('cloud_sso_user_id', $user->id)->orderBy('created_at', 'desc')->first()->update(['verify_status' => 2]);
                        if ($latestOtp) {
                            DB::connection($dbname)
                                ->table('tbl_otp_verifys')
                                ->where('id', $latestOtp->id)
                                ->update(['verify_status' => '2']);
                        }
                        return [
                            'status'     => false,
                            'message'    => 'validation error.',
                            'errors'     => ['error' => array('OTP Expired.')],
                            'statusCode' => 422,
                        ];
                    } else {
                        // $latestOtp = DB::connection($dbname)
                        //     ->table('tbl_otp_verifys')->where('cloud_sso_user_id', $user->id)->orderBy('created_at', 'desc')->first()->update(['verify_status' => 1]);
                        if ($latestOtp) {
                            DB::connection($dbname)
                                ->table('tbl_otp_verifys')
                                ->where('id', $latestOtp->id)
                                ->update(['verify_status' => '1']);
                        }
                        // return $this->sendResponse(null, 'OTP Verify successfully!');
                        return [
                            'status'     => true,
                            'data'       => null,
                            'message'    => 'OTP Verify successfully!',
                            'statusCode' => 200,
                        ];
                    }
                } else {
                    // return $this->sendError('validation error', ['error' => array('OTP Invalid.')], 422);
                    return [
                        'status'     => false,
                        'message'    => 'validation error.',
                        'errors'     => ['error' => array('OTP Invalid.')],
                        'statusCode' => 422,
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
