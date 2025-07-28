<?php

namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use App\Api\Customer\Modules\Settings\Models\SettingModule;
use App\Api\Customer\Modules\Settings\Models\SettingOption;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Jobs\SmtpConfigJob;
use App\Services\CompanyDatabaseService;


class CustomerSettingsService
{
    /**
     * Update SSO setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ssoSettingsUpdate(Request $request)
    {
        try {
            $companyId  = $request->get('companyId');
            $ssoSetting = SsoSettings::where('company_id', $companyId)->first();

            if (!$ssoSetting) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found',
                    'errors'     => ["error" => ["Company not found"]],
                    'statusCode' => 400
                ];
            }
            $ssoSetting->google_login    = $request->google_login;
            $ssoSetting->microsoft_login = $request->microsoft_login;
            $ssoSetting->save();

            return [
                'status'     => true,
                'message'    => 'SSO settings updated successfully',
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
     * Show SSO setting from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ssoSettingsShow(Request $request)
    {
        try {
            $companyId  = $request->get('companyId');
            $ssoSetting = SsoSettings::select('google_login', 'microsoft_login')->where('company_id', $companyId)->first();

            if (!$ssoSetting) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found',
                    'errors'     => ["error" => ["Company not found"]],
                    'statusCode' => 400
                ];
            }
            return [
                'status'     => true,
                'message'    => 'SSO settings updated successfully',
                'data'       => $ssoSetting,
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
     * Store a newly created SMTP global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function smtpGlobalSettingsCreate(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            // $encryptedPassword = Crypt::encrypt($request->smtp_password);
            $mapping = [
                'SMTPHost'                 => $request->smtp_host,
                'SMTPPort'                 => $request->smtp_gate,
                'SMTPUsername'             => $request->smpt_username,
                'SMTPPassword'             => $request->smtp_password,
                'SMTPEncryption'           => $request->encryption_type,
                'DefaultEmail'             => $request->default_email,
                'SMTPAuthentication'       => $request->authentication,
                'DefaultSystemSenderName'  => $request->default_system_sender_name,
                'DefaultSystemSenderEmail' => $request->default_system_sender_email,
            ];

            foreach ($mapping as $key => $value) {
                DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->where('variabel', $key)
                    ->update([
                        'vaerdi'   => $value,
                    ]);
            }

            $companyId  = $request->get('companyId');
            $mailConfig = Company::where('id',$companyId)->first();
            $mailConfig->mail_config = 0;
            $mailConfig->save();

            return [
                'status'     => true,
                'message'    => 'SMTP Global settings created successfully',
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
     * Store a newly created SMTP global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function smsGlobalSettingsCreate(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            // $encryptedPassword = Crypt::encrypt($request->sms_password);
            $mapping = [
                'SMSHost'      => $request->sms_host,
                'SMSPort'      => $request->sms_gate,
                'SMSLogontype' => $request->sms_logon_type,
                'SMSUsername'  => $request->sms_username,
                'SMSPassword'  => $request->sms_password,
            ];

            foreach ($mapping as $key => $value) {
                DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->where('variabel', $key)
                    ->update([
                        'vaerdi'   => $value,
                    ]);
            }
            return [
                'status'     => true,
                'message'    => 'SMS Global settings created successfully',
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
     * Show Global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showSmtpGlobalSetting(Request $request)
    {
        try {
            $dbName  = $request->get('dbName');
            $mapping = [
                'SMTPHost',
                'SMTPPort',
                'SMTPUsername',
                'SMTPPassword',
                'SMTPEncryption',
                'DefaultEmail',
                'SMTPAuthentication',
                'DefaultSystemSenderName',
                'DefaultSystemSenderEmail',
            ];
            $smtpGlobalSetting = [];
            foreach ($mapping as $key) {
                $result = DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->select('vaerdi')
                    ->where('variabel', $key)
                    ->first();
                if ($result) {
                    $smtpGlobalSetting[$key] = $result->vaerdi;
                } else {
                    $smtpGlobalSetting[$key] = null;
                }
            }

            return [
                'status'     => true,
                'message'    => 'Global settings details',
                'data'       => $smtpGlobalSetting,
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
     * Show Global setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showSmsGlobalSetting(Request $request)
    {
        try {
            $dbName  = $request->get('dbName');
            $mapping = [
                'SMSHost',
                'SMSPort',
                'SMSLogontype',
                'SMSUsername',
                'SMSPassword',
            ];
            $smsGlobalSetting = [];
            foreach ($mapping as $key) {
                $result = DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->select('vaerdi')
                    ->where('variabel', $key)
                    ->first();
                if ($result) {
                    $smsGlobalSetting[$key] = $result->vaerdi;
                } else {
                    $smsGlobalSetting[$key] = null;
                }
            }

            return [
                'status'     => true,
                'message'    => 'Global settings details',
                'data'       => $smsGlobalSetting,
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
     * Store a newly created user setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function userSettingsCreate(Request $request)
    {
        try {
            $dbName          = $request->get('dbName');
            $settingModuleId = $request->settingmodule_id;
            $userSetting     = SettingModule::on($dbName)->where('id', $settingModuleId)->first();
            $userSetting->settingstatus = $request->settingstatus;
            $userSetting->save();

            SettingModule::on($dbName)->where('id', '!=', $settingModuleId)
                ->update(['settingstatus' => 0]);

            $userSessionSetting  = SettingModule::on($dbName)->where('id', $settingModuleId)->first();
            $userSessionSetting->session_timeout = $request->session_timeout;
            $userSessionSetting->save();
            
            $mapping = [];
            if ($settingModuleId == 1) {
                $mapping = [
                    'SMTPHost',
                    'SMTPPort',
                    'SMTPUsername',
                    'SMTPPassword',
                    'SMTPEncryption',
                    'DefaultEmail',
                    'SMTPAuthentication',
                    'DefaultSystemSenderName',
                    'DefaultSystemSenderEmail'
                ];
            } elseif ($settingModuleId == 2) {
                $mapping = [
                    'SMSHost',
                    'SMSPort',
                    'SMSLogontype',
                    'SMSUsername',
                    'SMSPassword'
                ];
            }
            foreach ($mapping as $key) {
                DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->where('variabel', $key)
                    ->update(['setting_module_id' => $settingModuleId]);

                $userSettingOptions = SettingOption::on($dbName)->where('optionname', $key)->first();
                $userSettingOptions->setting_module_id  = $settingModuleId;
                $userSettingOptions->save();
            }
            return [
                'status'     => true,
                'message'    => 'User settings created successfully',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user settings',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Show user and sso setting in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function showUserSetting(Request $request)
    {
        try {

            $dbName      = $request->get('dbName');
            $userSetting = SettingModule::on($dbName)->get();
            return [
                'status'     => true,
                'message'    => 'User settings details',
                'data'       => $userSetting,
                'statusCode' => 200
            ];
        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while show user settings',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    /**
     * Show Global setting Values Details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getGlobalSettingValues(Request $request)
    {
        try {
            $dbName      = $request->get('dbName');
            $userSetting = SettingModule::on($dbName)->select('id', 'settingname')
                ->where('settingstatus', 1)->first();
            $settingOption = SettingOption::on($dbName)->where('setting_module_id', $userSetting->id)->get();
            $settingValues = DB::connection($dbName)
                ->table('cloud_variabler')
                ->select('vaerdi', 'variabel')
                ->where('setting_module_id', $userSetting->id)
                ->get();
            $formattedSettingValues = $settingValues->mapWithKeys(function ($item) {
                return [$item->variabel => $item->vaerdi];
            });
            $settingOption = $settingOption->map(function ($option) use ($formattedSettingValues) {
                if (isset($formattedSettingValues[$option->optionname])) {
                    $option->value = $formattedSettingValues[$option->optionname];
                }
                return $option;
            });
            return [
                'status'     => true,
                'message'    => 'Global Settings Option Details',
                'data'       => $settingOption,
                'statusCode' => 200
            ];
        } catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while get global settings values',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }
    public function testConfigMail(Request $request)
    {
        try {
            $companyId = $request->get('companyId');
            $company   = Company::select('company_name','mail_config')->where('id',$companyId)->first();  
            $userId    = $request->get('userId');
            $dbName    = $request->get('dbName');
            $user      =  DB::connection($dbName)
                    ->table('cloud_sso_users')
                    ->select('email')
                    ->where('id', $userId)
                    ->first();

            $companyDatabaseService = app(CompanyDatabaseService::class);
            SmtpConfigJob::dispatch($user->email,$companyId,$company->company_name,$companyDatabaseService);

            return [
                'status'     => true,
                'message'    => 'Test Email Sent to User Email',
                'data'       => null,
                'statusCode' => 200
            ];
        }
        catch (\Exception $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while sent email',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }
}
