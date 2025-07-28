<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;

class AppAccessandActivity
{
	public function customerAppAccess(Request $request)
	{
		try {

			$dbName    = $request->get('dbName');
			$userId    = $request->get('userId');
			$companyId = $request->get('companyId');
			if (!$companyId) {
				return [
					'status'     => false,
					'message'    => 'Unauthorised',
					'errors'     => ["error" => ["Company ID not found in token"]],
					'statusCode' => 401,
				];
			}

			$ssoLoginAccess = SsoSettings::select('crm_setting', 'cms_setting', 'shop_setting')
			->where('company_id', $companyId)
			->first();
			if (!$ssoLoginAccess) {
				return [
					'status'     => false,
					'message'    => 'Settings not found',
					'errors'     => ["error" => ["SSO settings not found for company"]],
					'statusCode' => 404,
				];
			}

			$userSiteAccess = DB::connection($dbName)
			->table('cloud_sso_users')
			->select('siteaccess')
			->where('id', $userId)
			->first();
			$siteTypes = config('app.siteaccess');
			$accessArray = explode('-', $userSiteAccess->siteaccess);
			$accessMapping = [];
			$index = 0;
			foreach ($siteTypes as $key => $value) {
				$accessMapping[$key] = isset($accessArray[$index]) ? (int)$accessArray[$index] : 0;
				$index++;
			}
			$deniedAccess = [];
			foreach ($accessMapping as $type => $hasAccess) {
				if ($hasAccess == 0) {
					$deniedAccess[] = ucfirst($type);
				}
			}
			if (!empty($deniedAccess)) {
				$deniedList = implode(', ', $deniedAccess);
				return [
					'status'     => false,
					'message'    => 'User does not have access to: {$deniedList}',
					'errors'     => ['error' => 'User does not have access to: {$deniedList}'],
					'statusCode' => 403
				]; 		
			}

			$responseData = [
				'crm'  => ['access' => false, 'url' => ''],
				'cms'  => ['access' => false, 'url' => ''],
				'shop' => ['access' => false, 'url' => ''],
			];

			$settingsMap = [
				'crm_setting'  => ['key' => 'crm',  'variabel' => 'cloud_crm_URL'],
				'cms_setting'  => ['key' => 'cms',  'variabel' => 'wlanshop_URL'],
				'shop_setting' => ['key' => 'shop', 'variabel' => 'profilURL'],
			];

			$activeKeys = [];
			foreach ($settingsMap as $settingKey => $details) {
				if ($ssoLoginAccess->$settingKey) {
					$activeKeys[] = $details['variabel'];
				}
			}

			if (!empty($activeKeys)) {
				$cloudVariables = DB::connection($dbName)
				->table('cloud_variabler')
				->whereIn('variabel', $activeKeys)
				->pluck('vaerdi', 'variabel');

				foreach ($settingsMap as $settingKey => $details) {
					if ($ssoLoginAccess->$settingKey && isset($cloudVariables[$details['variabel']])) {
						$responseData[$details['key']] = [
							'access' => true,
							'url'    => $cloudVariables[$details['variabel']],
						];
					}
				}
			}
			return [
				'status'     => true,
				'message'    => 'Settings retrieved successfully',
				'data'       => $responseData,
				'statusCode' => 201
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