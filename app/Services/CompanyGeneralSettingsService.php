<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Crypt;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;


class CompanyGeneralSettingsService
{
    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }

    /**
     * Display a paginated listing of the companies general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyGeneralList($companyId, Request $request)
    {
        try {
            // $company_id = crypt::decrypt($request->companyId);
            $company_id = $companyId;
            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
            $general_settings_type = $request->input('type', 0);

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
                0 => 'variabel',
                1 => 'vaerdi',
                2 => 'beskrivelse',
                3 => 'id',
                4 => 'company_type',
            );

            $order = $request->input('order_column') ?: 'id';
            $orderDirection = $request->input('order_dir') ?: 'asc';

            $currentPage = $request->input('page') ?: '1';
            $perPage = $request->input('length') ?: env("TABLE_LIST_LENGTH");

            $start = ($currentPage - 1) * $perPage;
            $sSearch = $request->input('search', '');

            // Run the query on the configured dynamic connection
            $ssoSettingsData = DB::connection($dbDetails->db_name)
                ->table('cloud_variabler')
                ->select($columns)
                ->where('type', 1)
                ->when($general_settings_type == 0, function ($query) {
                    return $query->whereIn('company_type', [1, 2, 3]);
                }, function ($query) use ($general_settings_type) {
                    return $query->where('company_type', $general_settings_type);
                });

            // Apply search filter if a search term is provided
            if (!empty($sSearch)) {
                $ssoSettingsData->where(function ($q) use ($sSearch, $columns) {
                    foreach ($columns as $key => $value) {
                        if ($key == 3) {
                            $q->where($value, 'like', '%' . $sSearch . '%');
                        } else {
                            $q->orWhere($value, 'like', '%' . $sSearch . '%');
                        }
                    }
                });
            }

            $ssoSettingsData->orderBy($order, $orderDirection);

            $totalData = $ssoSettingsData->count();

            // Clone the query before applying offset and limit for accurate total count
            $ssoSettingsDatas = $ssoSettingsData->offset($start)
                ->limit($perPage)
                ->get();
            // $totalData = $ssoSettingsData->count();
            $totalFiltered = $totalData;

            $data = [];
            if ($ssoSettingsDatas->isNotEmpty()) {
                $company_type = config('app.type');
                foreach ($ssoSettingsDatas as $ssoSettings) {
                    $ssoData = [
                        'variabel' => $ssoSettings->variabel,
                        'vaerdi' => $ssoSettings->vaerdi,
                        'beskrivelse' => $ssoSettings->beskrivelse,
                        'Type' =>  $company_type[$ssoSettings->company_type],
                        'encryptedSsoId' => Crypt::encrypt($ssoSettings->id),
                        'id' => $ssoSettings->id,
                        'encryptedCompanyId' => Crypt::encrypt($company_id),
                        'company_id' => $company_id,
                    ];
                    $data[] = $ssoData;
                }
            }

            $response = [
                "list" => $data,
                'currentPage' => $currentPage,
                'totalPages' => ceil($totalFiltered / $perPage),
                'recordsTotal' => $totalFiltered,
            ];
            return [
                'status'     => true,
                'message'    => 'Company Gereral settings list',
                'data'       => $response,
                'statusCode' => 200
            ];
        } catch (\Throwable $th) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    /**
     * Display a single company General settings record by ID.
     *
     * @param    $userId $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyGeneralView(Request $request)
    {
        try {
            // $ssoSettingsId =  Crypt::decrypt($request->userId);
            $ssoSettingsId = $request->userId;
            // $company_id =  Crypt::decrypt($request->companyId);
            $company_id =  $request->companyId;
            $dbDetails =  CompanyDatabase::where('company_id', $company_id)->first();

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

            $ssoSettingsData = DB::connection($dbDetails->db_name)
                ->table('cloud_variabler')
                ->where('id', $ssoSettingsId)
                ->first();

            if ($ssoSettingsData) {
                // $company_id =  Crypt::decrypt($request->query('companyId'));
                return [
                    'status'     => true,
                    'message'    => 'company general settings data',
                    'data'       => $ssoSettingsData,
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
                'message'    => 'An error occurred while creating the user',
                'errors'     => ['error' => $th->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    public function companyGeneralDelete($request)
    {
        $general_setting_id = $request->generalSettingId;
        // $general_setting_id = crypt::decrypt($request->generalSettingId);
        $company_id = $request->companyId;
        // $company_id = crypt::decrypt($request->companyId);

        if (empty($company_id)) {
            return [
                'status' => false,
                'message' => 'Invalid company ID.',
                'errors' => ['Invalid company ID.'],
                'statusCode' => 400,
            ];
        }

        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);

        if (!$dbDetails) {
            return [
                'status' => false,
                'message' => 'Validation Error.',
                'errors' => ['Database details not found.'],
                'statusCode' => 400,
            ];
        }

        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['MySQL connection failed.'],
                'statusCode' => 500,
            ];
        }

        try {
            DB::connection($dbDetails->db_name)->table('cloud_variabler')
                ->where('id', $general_setting_id)
                ->delete();

            return [
                'status'     => true,
                'data'       => null,
                'message'    => 'Deleted successfully.',
                'statusCode' => 200,
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while deleting the role.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function companyGeneralUpdate($request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'generalSettingVariable' => 'required',
                'generalSettingValue' => 'required',
            ], [
                'generalSettingVariable.required' => 'Variable is required',
                'generalSettingValue.required' => 'Value is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => $validator->errors(),
                    'statusCode' => 400,
                ];
            }

            $sso_variable_name = $request->generalSettingVariable ?? '';
            $sso_value_name = $request->generalSettingValue ?? '';
            $sso_description_name = $request->generalSettingDescription ?? '';
            $sso_company_type = $request->generalSettingCompanyType ?? '1';
            $sso_setting_id = $request->userId;
            $company_id = $request->companyId;

            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);

            if (!$dbDetails) {
                return [
                    'status' => false,
                    'message' => 'Validation Error.',
                    'errors' => ['Database details not found.'],
                    'statusCode' => 400,
                ];
            }

            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                return [
                    'status' => false,
                    'message' => 'Server Error.',
                    'errors' => ['MySQL connection failed.'],
                    'statusCode' => 500,
                ];
            }

            $affectedRows = DB::connection($dbDetails->db_name)
                ->table('cloud_variabler')
                ->where('id', $sso_setting_id)
                ->update([
                    'beskrivelse' => $sso_description_name,
                    'variabel' => $sso_variable_name,
                    'vaerdi' => $sso_value_name,
                    'company_type' => $sso_company_type,
                ]);
            if (($sso_setting_id) == 97) {
                Company::where('id', $company_id)->update([
                    'domain_name' => $sso_value_name,
                ]);
            }

            // dd($sso_company_type);
            if ($affectedRows) {
                return [
                    'status' => true,
                    'data'       => null,
                    'message' => 'Company general settings updated successfully.',
                    'statusCode' => 200,
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Error.',
                    'errors' => ['Please make any changes and update'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server Error.',
                'errors' => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
