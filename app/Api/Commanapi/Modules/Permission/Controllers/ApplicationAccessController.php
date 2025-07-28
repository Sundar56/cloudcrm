<?php

namespace App\Api\Commanapi\Modules\Permission\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use App\Http\Controllers\Api\BaseController;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use Spatie\Permission\Models\Role;

class ApplicationAccessController extends BaseController
{
    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function checkApplicationAccess(Request $request)
    {
        try {
            $token   = $request->accessToken;
            $decoded = JWTAuth::setToken($token)->getPayload();

            if ($decoded->get('exp') < time()) {
                return $this->sendError('Unauthorised.', ['error' => 'Token has expired'], 401);
            }
            $request->attributes->add(['accessToken' => $decoded]);
            $userId    = $request->attributes->get('accessToken')->get('id');
            $companyId = $request->attributes->get('accessToken')->get('companyId');

            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID not found in token"]],
                    'statusCode' => 401,
                ];
            }
            $siteType = $request->siteType;
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
            $siteSettings = [
                'crm'  => 'crm_setting',
                'cms'  => 'cms_setting',
                'shop' => 'shop_setting',
            ];

            if (!isset($siteSettings[$siteType])) {
                return [
                    'status'     => false,
                    'message'    => 'Invalid site type',
                    'errors'     => ["error" => ["Unsupported site type provided"]],
                    'statusCode' => 400,
                ];
            }

            // Check if the site type is enabled
            if ($ssoLoginAccess->{$siteSettings[$siteType]} == 0) {
                return [
                    'status'     => false,
                    'message'    => ucfirst($siteType) . ' access is not allowed for this company.',
                    'errors'     => ["error" => [ucfirst($siteType) . " setting is disabled"]],
                    'statusCode' => 403,
                ];
            }

            $connection = $this->companyDatabaseService->connect($companyId);
            $dbName = $connection['dbName'];
            $cloudVariablerValues = DB::connection($dbName)
                ->table('cloud_variabler')
                ->whereIn('variabel', ['SiteDomain', 'GemCookieDage'])
                ->pluck('vaerdi', 'variabel');

            $cloudUserDetails = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->select('brugernavn', 'password', 'siteaccess', 'userlevel')
                ->where('id', $userId)->first();
            $userRole = Role::select( 'name')
                ->where('id', $cloudUserDetails->userlevel)->first();
            $userDetails = [
                'cloudVariablerValues'  => $cloudVariablerValues,
                'cloudUserDetails'      => $cloudUserDetails,
                'CloudUserRole'         => $userRole,
            ];
            return $this->sendResponse($userDetails, 'User Details retrieved successfully');
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->sendError('Unauthorised.', ['error' => 'Token has expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->sendError('Unauthorised.', ['error' => 'Token has invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->sendError('Unauthorised.', ['error' => 'Token could not be parsed'], 401);
        } catch (Exception $e) {
            return $this->sendError('Unauthorised.', ['error' => 'An error occurred while processing the token'], 401);
        }
    }
}
