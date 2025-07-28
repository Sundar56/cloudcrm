<?php

namespace App\Api\Commanapi\Modules\Permission\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermission;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Services\CompanyDatabaseService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\Commanapi\Modules\Permission\Models\ApiHistory;
use App\Services\CompanyModuleAccessApiService;
use App\Http\Controllers\Api\BaseController;

class CompanyModuleAccessController extends BaseController
{
    protected $companyDatabaseService;
    protected $CompanyModuleAccessApiService;
    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }
    public function CheckCompanyModuleAccess(Request $request)
    {

        // $token = $request->token; // Get token from Authorization header
        // $decoded = JWTAuth::setToken($token)->getPayload();
        // return $decoded;

        $validator = Validator::make($request->all(), [
            'apiKey' => 'required',
            'apiSecret' => 'required',
            'siteType' => 'required',
            'module' => 'required',
            'userId' => 'required',
        ], [
            'apiKey.required' => 'apiKey is required',
            'apiSecret.required' => 'apiSecret is required',
            'siteType.required' => 'siteType is required',
            'module.required' => 'Module is required',
            'userId.required' => 'user is requireds',
        ]);

        if ($validator->fails()) {
            $this->logApiHistory($request, 422, ['error' => 'payload missing']);
            return response()->json(['error' => $validator->errors()], 422);
        }

        $api_key =  $request->apiKey;
        $api_secret = $request->apiSecret;
        $site_type = $request->siteType;
        $module = $request->module;
        $user_id = $request->userId;
        $sub_module = $request->subModule??null;

        $check_company_exist = Company::where([['apikey', $api_key], ['apisecret', $api_secret]])->first();
        if (!$check_company_exist) {

            $this->logApiHistory($request, 401, ['error' => 'Company does not exist']);
            return response()->json(['error' => 'Company does not exist', 'status' => 401], 401);
        }


        $company_id =  $check_company_exist->id;

        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);

        if (!$dbDetails) {
            $this->logApiHistory($request, 404, ['error' => 'Database details not found.']);
            return response()->json(['error' => 'Database details not found.', 'status' => 404], 404);
        }

        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);


        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            $this->logApiHistory($request, 500, ['error' => 'MySQL connection failed']);
            return response()->json(['error' => 'MySQL connection failed', 'status' => 500], 500);
        }

        $user_details = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->select('userlevel')
            ->where('id', $user_id)
            ->first();

        if (!$user_details) {
            $this->logApiHistory($request, 404, ['error' => 'User not found']);
            return response()->json(['error' => 'User not found', 'status' => 404], 404);
        }

        $user_role_id = $user_details->userlevel;
        $permission = $site_type . '.' . $module. ($sub_module ? "_$sub_module" : '')  . '.index';
        $permission_id = Permission::where('name', $permission)->pluck('id')->first();
        if (isset($permission_id)) {
            $hasPermission = RoleHasPermission::where('permission_id', $permission_id)->where('role_id', $user_role_id)->first();
            if (isset($hasPermission)) {
                $this->logApiHistory($request, 200, ['Message' => 'Access Granted', 'status' => 200]);
                $customClaims = [
                    'company_id' => $check_company_exist->id,
                    'company_name' => $check_company_exist->company_name, // optional
                    'iss' => 'crmsystem',
                    'iat' => now()->timestamp,
                    'permission' => 1
                ];
                // $token = JWTAuth::claims($customClaims)->fromUser($check_company_exist);
                $token = '';
                return response()->json(['token' => $token, 'Message' => 'Access Granted', 'status' => 200], 200);
            } else {
                $this->logApiHistory($request, 402, ['error' => 'Access Denied', 'status' => 402]);
                return response()->json(['error' => 'Access Denied', 'status' => 402], 402);
            }
        } else {
            $this->logApiHistory($request, 404, ['error' => 'Your requested module did not exist', 'status' => 404]);
            return response()->json(['error' => 'your request Module did not exist', 'status' => 404], 404);
        }

        $this->logApiHistory($request, 500, ['error' => 'Something went wrong', 'status' => 500]);
        return response()->json(['error' => 'Something Went Worng', 'status' => 500], 500);
    }

    private function logApiHistory(Request $request, $statusCode, $response)
    {
        $req = $request->header();
        ApiHistory::create([
            'user_agent' => $request->header('User-Agent') ?? null,
            'request_payload' => $request->all() ?? null,
            'status_code' => $statusCode ?? null,
            'response_payload' => is_array($response) || is_object($response) ? $response : null,
            'url' => $request->url() ?? null,
            'http_method' => $request->method() ?? null,
            'error_message' => $response['error'] ?? null,
            'x-forwarded-for' => isset($req["x-forwarded-for"]) ? $req["x-forwarded-for"][0] : '',
            'accept-encoding' => isset($req["accept-encoding"]) ? $req["accept-encoding"][0] : '',
            'accept' => isset($req["accept"]) ? $req["accept"][0] : '',
            'connection' => isset($req["connection"]) ? $req["connection"][0] : '',
            'x-forwarded-server' => isset($req["x-forwarded-server"]) ? $req["x-forwarded-server"][0] : '',
            'x-forwarded-host' => isset($req["x-forwarded-host"]) ? $req["x-forwarded-host"][0] : '',
            'host' => isset($req["host"]) ? $req["host"][0] : ''
        ]);
    }
}
