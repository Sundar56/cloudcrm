<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Customer\Modules\Employees\Models\Employees;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use App\Services\CompanySsoSettingservice;
use App\Api\Customer\Modules\Settings\Models\SettingModule;
use App\Api\Customer\Modules\SocialAccounts\Models\AppModule;
use App\Api\Customer\Modules\SocialAccounts\Models\AppVariable;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;

class GoogleService
{
	protected $companyDatabaseService;
	protected $provider;
	protected $companySsoSettingService;

	public function __construct(CompanyDatabaseService $companyDatabaseService, CompanySsoSettingservice $companySsoSettingService)
	{
		$this->companyDatabaseService = $companyDatabaseService;
		$this->provider = 'google';
		$this->companySsoSettingService = $companySsoSettingService;
	}
	public function accessGoogleLogin(Request $request)
	{
		try {
			$googleToken    = $request->token;
			$token          = explode('.', $googleToken);
			$decodedPayload = base64_decode(strtr($token[1], '-_', '+/'));
			$decodedData    = json_decode($decodedPayload, true);
			$email          = $decodedData['email'];

			$domainName = $request->domain_name;
			$company    = Company::select('id', 'company_name')
				->where('domain_name', $domainName)
				->first();

			if (!$company) {
				return [
					'status'     => false,
					'message'    => 'Company not found via Google login',
					'errors'     => ["error" => array("Company details not found via Google login")],
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
			}

			// Check if user exists in the database
			$user = Employees::on($dbDetails->db_name)
				->select('id', 'navn', 'email', 'brugernavn', 'mfa')
				->where('email', $email)
				->first();

			if (!$user) {
				$errorData = [
					'status'     => false,
					'message'    => 'No user found with this email address via Google login',
					'errors'     =>  ["error" => array("User not found via Google login")],
					'statusCode' => 400
				];
				$failedData = new FailedLogin([
					'email'         => $email,
					'error_details' => json_encode($errorData),
					'failedat'      => now(),
				]);
				$failedData->setConnection($dbDetails->db_name)->save();

				return [
					'status'     => false,
					'message'    => 'No user found with this email address via Google login',
					'errors'     =>  ["error" => array("User not found via Google login")],
					'statusCode' => 400
				];
			}
			$employee        = new Employees();
			$employee->id    = $user->id;
			$employee->navn  = $user->navn;
			$employee->email = $user->email;
			$employee->brugernavn = $user->brugernavn;

			$customClaims = [
				'id'        => $employee->id,
				'email'     => $employee->email,
				'name'      => $employee->navn,
				'iss'       => 'CustomerPortal',
				'iat'       => (int) now()->timestamp,
				'companyId' => $companyId,
			];

			$mfa = 0;
			$checkMfaExisting  =  SettingModule::on($dbDetails->db_name)
				->where('settingstatus', 1)->first();
			if ($checkMfaExisting) {
				if ($user->mfa == 1) {
					$mfa = 1;
				}
			}

			$token = JWTAuth::claims($customClaims)->fromUser($employee);
			$userData = [
				'username'    => $employee->brugernavn,
				'email'       => $employee->email,
				'userId'      => $employee->id,
				'token'       => $token,
				'companyId'   => $companyId,
				'companyName' => $company->company_name,
				'expire_in'   => config('jwt.ttl') * 60
			];
			$lastRecord = UserloginActivity::on($dbDetails->db_name)->where('userid',$employee->id)->latest()->first();
        
            if(!$lastRecord->logouttime){
                $tokenExpiry = config('jwt.ttl');
                $loginTime   = Carbon::parse($lastRecord->logintime);
                $logoutTime  = $loginTime->addMinutes($tokenExpiry);

                $lastRecord->logouttime = $logoutTime;
                $lastRecord->duration   = DB::raw("TIMESTAMPDIFF(SECOND, logintime, '$logoutTime')");
                $lastRecord->save();
            }

            $loginTime   = now();
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
				'data'       => $userData,
				'message'    => 'Company user login successfully via Google',
				'statusCode' => 200,
			];
		} catch (\Exception $e) {
			$errorData = [
				'status'     => false,
				'message'    => 'An error occurred via Google login',
				'errors'     => ['error' => $e->getMessage()],
				'statusCode' => 500,
			];

			$failedData = new FailedLogin([
				'email'         => $decodedData['email'],
				'error_details' => json_encode($errorData),
				'failedat'      => now(),
			]);
			$failedData->setConnection($dbDetails->db_name)->save();

			return [
				'status'     => false,
				'message'    => 'An error occurred via Google login',
				'errors'     => ['error' => $e->getMessage()],
				'statusCode' => 500,
			];
		}
	}
	public function checkAppExisting($dbName)
	{
		return AppModule::on($dbName)->where([['appname', $this->provider], ['appstatus', 1]])->first();
	}
	public function updateGoogleClientId(Request $request)
	{
		try {
			$companyId     = $request->get('companyId');
			$dbName        = $request->get('dbName');
			$googleAccess  = SsoSettings::where('company_id', $companyId)->first();
			if ($googleAccess->google_login == 1) {
				$googleClientId    = $request->google_client_id ?? null;
				$googleSecretId    = $request->google_secret_id ?? null;
				$googleRedirectUrl = $request->google_redirect_url ?? null;

				$this->companySsoSettingService->updateAppVariable($dbName, 'google', 'googleClientId', $googleClientId);
				$this->companySsoSettingService->updateAppVariable($dbName, 'google', 'googleSecretId', $googleSecretId);
				$this->companySsoSettingService->updateAppVariable($dbName, 'google', 'googleRedirectUrl', $googleRedirectUrl);
			} else {
				return [
					'status'     => false,
					'message'    => 'Google Credentials Not updated',
					'errors'     => ["error" => array("Google Credentials missing or disabled")],
					'statusCode' => 404,
				];
			}
			return [
				'status'     => true,
				'message'    => 'Google Credentials Updated Successfully',
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
	public function showGoogleClientId(Request $request)
	{
		try {
			$companyId      = $request->get('companyId');
			$dbName         = $request->get('dbName');
			$googleClientId = $this->checkAppExisting($dbName);

			if ($googleClientId) {
				$appVariable = AppVariable::on($dbName)->where(
					[
						['tbl_appmodule_id', $googleClientId->id]
					]
				)->get();
				return [
					'status'     => true,
					'message'    => 'Google Credentials',
					'data'       => $appVariable,
					'statusCode' => 200
				];
			} else {
				return [
					'status'     => false,
					'message'    => 'Google app missing or disabled',
					'errors'     => ['error' => 'Google app missing or disabled'],
					'statusCode' => 400,
				];
			}
		} catch (\Exception $th) {
			return [
				'status'     => false,
				'message'    => 'An error occurred while creating the user',
				'errors'     => ['error' => $th->getMessage()],
				'statusCode' => 500
			];
		}
	}
}
