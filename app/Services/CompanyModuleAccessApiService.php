<?php

namespace App\Services;

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

class CompanyModuleAccessApiService
{
    protected $companyDatabaseService;
    protected $CompanyFileUploadService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }

    public function CheckCompanyModuleAccess(Request $request)
    {

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
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => $validator->errors(),
                'statusCode' => 422
            ];
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
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => ['error' => 'Company does not exist', 'status' => 0],
                'statusCode' => 404
            ];
        }


        $company_id =  $check_company_exist->id;

        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);

        if (!$dbDetails) {
            $this->logApiHistory($request, 404, ['error' => 'Database details not found.']);
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => ['error' => 'Database details not found.', 'status' => 0],
                'statusCode' => 404
            ];
            
        }

        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);


        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            $this->logApiHistory($request, 500, ['error' => 'MySQL connection failed']);
            return response()->json(['error' => 'MySQL connection failed', 'status' => 0], 500);
            return [
                'status'     => false,
                'message'    => 'Server Error',
                'errors'     => ['error' => 'MySQL connection failed', 'status' => 0],
                'statusCode' => 500
            ];
        }

        $user_details = DB::connection($dbDetails->db_name)
            ->table('cloud_sso_users')
            ->select('userlevel')
            ->where('id', $user_id)
            ->first();

        if (!$user_details) {
            $this->logApiHistory($request, 404, ['error' => 'User not found']);
            return [
                'status'     => false,
                'message'    => 'validation Error',
                'errors'     => ['error' => 'User not found', 'status' => 0],
                'statusCode' => 404
            ];
        }

        $user_role_id = $user_details->userlevel;
        $permission = $site_type . '.' . $module. ($sub_module ? "_$sub_module" : '')  . '.index';
        $permission_id = Permission::where('name', $permission)->pluck('id')->first();
        if (isset($permission_id)) {
            $hasPermission = RoleHasPermission::where('permission_id', $permission_id)->where('role_id', $user_role_id)->first();
            if (isset($hasPermission)) {
                $this->logApiHistory($request, 200, ['Message' => 'Access Granted', 'status' => 1]);
                
                $customClaims = [
                    'company_id' => $check_company_exist->id,
                    'company_name' => $check_company_exist->company_name, // optional
                    'iss' => 'CrmSystemAdmin',
                    'iat' => now()->timestamp,
                    'permission' => 1
                ];
                $token = JWTAuth::claims($customClaims)->fromUser($check_company_exist);
                // $token = '';
                // return response()->json(['token' => $token, 'Message' => 'Access Granted', 'status' => 1], 200);
                return [
                    'status'     => true,
                    'message'    => 'User created successfully',
                    'data'       => ['token' => $token, 'Message' => 'Access Granted', 'status' => 1],
                    'statusCode' => 200
                ];
            } else {
                $this->logApiHistory($request, 402, ['error' => 'Access Denied', 'status' => 0]);
                return response()->json(['error' => 'Access Denied', 'status' => 0], 402);
                return [
                    'status'     => false,
                    'message'    => 'Unauthorized',
                    'errors'     => ['error' => 'Access Denied', 'status' => 0],
                    'statusCode' => 403
                ];
            }
        } else {
            $this->logApiHistory($request, 404, ['error' => 'Your requested module did not exist', 'status' => 0]);
            return [
                'status'     => false,
                'message'    => 'validation Error',
                'errors'     =>['error' => 'your request Module did not exist', 'status' => 0],
                'statusCode' => 404
            ];
        }

        $this->logApiHistory($request, 500, ['error' => 'Something went wrong', 'status' => 0]);
        return [
            'status'     => false,
            'message'    => 'Server Error',
            'errors'     => ['error' => 'Something Went Worng', 'status' => 0],
            'statusCode' => 500
        ];
        
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