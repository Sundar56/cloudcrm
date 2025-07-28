<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Api\Systemadmin\Modules\Adminuser\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Jobs\SendEmailJob;
use Illuminate\Container\RewindableGenerator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserOtpVerify;
use App\Jobs\SendVerifyOtpMailJob;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\DataSecurityService;



class AuthController extends BaseController
{
    protected $guard;
    protected $DataSecurityService;

    public function __construct(DataSecurityService $DataSecurityService)
    {
        $this->guard = "api";
        $this->DataSecurityService =  $DataSecurityService;
    }
    /**
     * Handle the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 400);
            }

            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $password =  $request->password;
            // Check if the user with the given username exists
            // $user = User::where($fieldType, $request->username)->first();
            $user = User::select('id', 'name', 'email', 'user_displayname', 'user_phone', 'password', 'two_factor_authentication')->where($fieldType, $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->sendError('Unauthorised.', ['error' => array('The username or password is incorrect.')], 400);
            }

            $roles  = DB::table('model_has_roles')->where('model_id', $user->id)->first();
            $roleId = $roles->role_id;
            $roleName = Role::select('display_name')->where('id', $roleId)->first();
            $permissionData = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id', $roleId)
                ->pluck('permissions.name')
                ->toArray();

            $customClaims = [
                'id'     => $user->id,
                'email'  => $user->email,
                'name'   => $user->name,
                'roleId' => $roleId,
                'iss'    => 'SystemAdmin',
                'iat'    => (int) now()->timestamp,
            ];

            $token = JWTAuth::claims($customClaims)->fromUser($user);

            $userData = [
                'name'         => $user->name,
                'userId'       => $user->id,
                'email'        => $user->email,
                'display_name' => $user->user_displayname,
                'role_name'    => $roleName->display_name,
                'roleId'       => $roleId,
                'permission'   => $permissionData,
                'tfa'          => $user->two_factor_authentication,
                'token'        => $token,
                'expire_in'    => config('jwt.ttl') * 60
                
            ];

            $encryptedData = $this->DataSecurityService->encrypt($userData);
            return $this->sendResponse($encryptedData, 'Login successful');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
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
                return $this->sendError('Validation Error.', $validator->errors(), 400);
            }

            $oldPassword = $request->old_password;
            $newPassword = $request->new_password;

            $userId = $request->attributes->get('decoded_token')->get('id');
            $user = User::where('id', $userId)->first();
            // $user = auth($this->guard)->user();
            if (!Hash::check($oldPassword, $user->password)) {
                return $this->sendError('Validation Error.', ["old_password" => array("Old Password Doesn't match!")], 400);
            }

            if (strcmp($oldPassword, $newPassword) == 0) {
                return $this->sendError('Validation Error.', ["new_password" => array("New Password cannot be same as your current password")], 400);
            }

            $user->password = $newPassword;
            $user->save();

            return $this->sendResponse([], "Password changed successfully !");
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle the logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logOut(Request $request)
    {
        try {
            $token = auth($this->guard)->getToken();

            $invalidate = auth($this->guard)->invalidate($token);

            if ($invalidate) {
                return $this->sendResponse([], 'Successfully logout');
            }
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
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
                'username' => 'required|email|exists:users,email',
            ], [
                'username.exists' => 'User not found',
                'username.email' => 'Must be Valid Email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }
            $user = User::where('email', $request->username)->first();
            $newPassword = Str::random(10);
            $user->password = Hash::make($newPassword);
            $user->save();

            SendEmailJob::dispatch($user->email, $newPassword, $user->name,'System Portal ForgotPassword','system');

            return $this->sendResponse(null, 'Password reset email sent!');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
        }
    }

    public function otpSend(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $user = User::where($fieldType, $request->username)->first();
            // $userId = $request->attributes->get('decoded_token')->get('id');
            // $user = User::where('id', $userId)->first();
            if (!$user) {
                // Handle the case when the user is not found in the session
                return $this->sendError('validation error', ['error' => array('user not found')], 422);
            }
            $userId = Crypt::encrypt($user->id);
            $otp = random_int(1000, 9999);
            SendVerifyOtpMailJob::dispatch($user->email, $otp, $user->id, $user->name,'Adminuser Otp Verification','system');
            $created_at = Carbon::now()->format('Y-m-d H:i:s');
            $verify_otp_time = Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s');
            UserOtpVerify::insert([
                'otp' => $otp,
                'user_id' => $user->id,
                'verify_otp_time' => $verify_otp_time,
                'created_at' => $created_at,

            ]);
            return $this->sendResponse(null, 'OTP send to user mail successfully!');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
        }
    }

    public function otpVerify(Request $request)
    {
        try {
            $first = $request->first;
            $second = $request->second;
            $third = $request->third;
            $fourth = $request->fourth;
            // $userId = Crypt::decrypt($request->userId);
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $user = User::where($fieldType, $request->username)->first();
            $string_otp  = (int)$first . $second . $third . $fourth;
            $otp = (int)$string_otp;
            $latestOtp = UserOtpVerify::where('user_id', $user->id)
                ->orderBy('created_at', 'desc') // Sort by the latest record
                ->first();

            if ($latestOtp) {
                // Get the last inserted OTP
                $lastOtp = $latestOtp->otp;
                $user = User::where('id', $user->id)->first();
                if ($lastOtp === $otp) {

                    // Given date (you can retrieve this from your database or request)
                    $givenDate = strtotime(trim($latestOtp->verify_otp_time));

                    // Get the current time
                    $currentTime = strtotime(Carbon::now()->format('Y-m-d H:i:s'));

                    if ($currentTime > $givenDate) {
                        $latestOtp = UserOtpVerify::where('user_id', $user->id)->orderBy('created_at', 'desc')->first()->update(['verify_status' => 2]);
                        return $this->sendError('validation error', ['error' => array('OTP Expired.')], 422);
                    } else {
                        $latestOtp = UserOtpVerify::where('user_id', $user->id)->orderBy('created_at', 'desc')->first()->update(['verify_status' => 1]);
                        return $this->sendResponse(null, 'OTP Verify successfully!');
                    }
                } else {
                    return $this->sendError('validation error', ['error' => array('OTP Invalid.')], 422);
                }
            }
        } catch (\Exception $e) {
            return $this->sendError('An error occurred.', ['error' => $e->getMessage()], 500);
        }
    }
}
