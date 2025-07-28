<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use App\Services\CompanyFileUploadService;
use Illuminate\Support\Str;
use App\Api\Systemadmin\Modules\Company\Models\Userimage;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use App\Api\Customer\Modules\SocialAccounts\Models\AppVariable;
use App\Api\Customer\Modules\SocialAccounts\Models\AppModule;
use App\Api\Customer\Modules\SocialAccounts\Models\AppOption;


class CompanySsoSettingservice
{
    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Store a newly created company sso settings access in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function companySsoSetting(Request $request)
    {
        try {
            // $company_id = Crypt::decrypt($request->company_id);
            $company_id     = $request->company_id;
            $crmSetting     = $request->crm_setting;
            $cmsSetting     = $request->cms_setting;
            $shopSetting    = $request->shop_setting;
            $microsoftLogin = $request->microsoft_login;
            $googleLogin    = $request->google_login;

            $company = SsoSettings::where('company_id', $company_id)->first();

            if ($company) {
                $company->update([
                    'crm_setting'     => $crmSetting,
                    'cms_setting'     => $cmsSetting,
                    'shop_setting'    => $shopSetting,
                    'microsoft_login' => $microsoftLogin,
                    'google_login'    => $googleLogin,
                    'updated_at'      => now(),
                ]);
                $message = 'SSO settings updated successfully';
            } else {
                SsoSettings::create([
                    'company_id'      => $company_id,
                    'crm_setting'     => $crmSetting,
                    'cms_setting'     => $cmsSetting,
                    'shop_setting'    => $shopSetting,
                    'microsoft_login' => $microsoftLogin,
                    'google_login'    => $googleLogin,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $message = 'SSO settings created successfully';
            } 

            $dbDetails = $this->getDatabaseDetailsAndValidate($company_id);
            $this->updateLoginSettings($request, $dbDetails['db_name'], $googleLogin, $microsoftLogin);

            return [
                'status'     => true,
                'message'    => $message,
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
     * Display a company sso setting record by company id.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function companySsosettingView($companyId)
    {
        try {
            // $company_id = Crypt::decrypt($request->companyId);
            $data      = SsoSettings::where('company_id', $companyId)->first();
            if ($data) {
                return [
                    'status'     => true,
                    'message'    => 'Company SSO Settings data',
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
    * Update Google and Microsoft Credentials.
    *
    * @param  $companyId
    * @return \Illuminate\Http\JsonResponse
    */
    public function getDatabaseDetailsAndValidate($company_id)
    {
        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
        if (!$dbDetails) {
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => ["error" => ["Database details not found."]],
                'statusCode' => 422
            ];
        }

        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            return [
                'status'     => false,
                'message'    => 'Server Error',
                'errors'     => ["error" => ["MySQL connection failed."]],
                'statusCode' => 500
            ];
        }
     return ['status' => true, 'db_name' => $dbDetails->db_name]; // Return success with db name
 }

 public function updateLoginSettings(Request $request, $dbName, $googleLogin, $microsoftLogin)
 {

    if ($googleLogin == 1) {
        $googleClientId    = $request->google_client_id ?? null;
        $googleSecretId    = $request->google_secret_id ?? null;
        $googleRedirectUrl = $request->google_redirect_url ?? null;

        $this->updateAppVariable($dbName, 'google', 'googleClientId', $googleClientId);
        $this->updateAppVariable($dbName, 'google', 'googleSecretId', $googleSecretId);
        $this->updateAppVariable($dbName, 'google', 'googleRedirectUrl', $googleRedirectUrl);
    }

    if ($microsoftLogin == 1) {
        $microsoftClientId    = $request->microsoft_client_id ?? null;
        $microsoftSecretId    = $request->microsoft_secret_id ?? null;
        $microsoftRedirectUrl = $request->microsoft_redirect_url ?? null;

        $this->updateAppVariable($dbName, 'microsoft', 'clientId', $microsoftClientId);
        $this->updateAppVariable($dbName, 'microsoft', 'secretId', $microsoftSecretId);
        $this->updateAppVariable($dbName, 'microsoft', 'redirectUrl', $microsoftRedirectUrl);
    }
}
    public function updateAppVariable($dbName, $moduleName, $variableName, $value)
    {
        $moduleId = AppModule::on($dbName)
        ->select('id')
        ->where('appname', $moduleName)
        ->first();

        if ($moduleId) {
            AppVariable::on($dbName)
            ->where('tbl_appmodule_id', $moduleId->id)
            ->where('appvariable', $variableName)
            ->update(['appvalue' => $value]);
        }
    }
    public function checkAppExisting($dbName,$provider)
    {
        return AppModule::on($dbName)->where([['appname', $provider], ['appstatus', 1]])->first();
    }
    public function showLoginCredentials($companyId)
    {
        try {
            $loginAccess = SsoSettings::where('company_id', $companyId)->first();
            $dbDetails   = $this->getDatabaseDetailsAndValidate($companyId);
            $dbName      = $dbDetails['db_name'];
            $credentials = []; 


            if ($loginAccess->google_login == 1) {
                $googleAccess = $this->checkAppExisting($dbName, 'google');
                if ($googleAccess) {
                    $googleCredentials = AppVariable::on($dbName)->where(
                        'tbl_appmodule_id', $googleAccess->id
                    )->get();
                    $credentials['google'] = $googleCredentials;
                } else {
                    $credentials['google'] = ['error' => 'Google app missing or disabled'];
                }
            }

            if ($loginAccess->microsoft_login == 1) {
                $microsoftAccess = $this->checkAppExisting($dbName, 'microsoft');
                if ($microsoftAccess) {
                    $microsoftCredentials = AppVariable::on($dbName)->where(
                        'tbl_appmodule_id', $microsoftAccess->id
                    )->get();
                    $credentials['microsoft'] = $microsoftCredentials;
                } else {
                    $credentials['microsoft'] = ['error' => 'Microsoft app missing or disabled'];
                }
            }

            if (empty($credentials)) {
                return [
                    'status'     => false,
                    'message'    => 'No login credentials found',
                    'errors'     => ['error' => 'No credentials available'],
                    'statusCode' => 400,
                ];
            }

            return [
                'status'     => true,
                'message'    => 'Login Credentials',
                'data'       => $credentials,
                'statusCode' => 200
            ];

        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'Server error',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    public function customerFooterSetting(Request $request){

        try {

            $company_id    = $request->company_id;
            $footerSetting = $request->footer_setting;
            $ssoSetting    = SsoSettings::where('company_id', $company_id)->first();

            if ($ssoSetting) {
                $ssoSetting->footer_setting = $footerSetting;
                $ssoSetting->save();
            } else {
                SsoSettings::create([
                    'company_id'     => $company_id,
                    'footer_setting' => $footerSetting
                ]);
            }

            return [
                'status'     => true,
                'message'    => 'Footer setting updated',
                'data'       => null,
                'statusCode' => 200
            ];

        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'Server error',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }

    }
}
