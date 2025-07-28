<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Services\CompanyDatabaseService;
use App\Services\CompanyFileUploadService;
use Illuminate\Support\Facades\Validator;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Jobs\CreateCompanyDatabaseJob;
use App\Api\Systemadmin\Modules\Company\Models\OtherJobs;


class CompanyService
{
    protected $companyDatabaseService;
    protected $CompanyFileUploadService;

    public function __construct(CompanyDatabaseService $companyDatabaseService, CompanyFileUploadService $CompanyFileUploadService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->CompanyFileUploadService = $CompanyFileUploadService;
    }

    /**
     * Display a paginated listing of the companies.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyList(Request $request)
    {
        try {

            // Validate input
            $validator = Validator::make($request->all(), [
                'page' => 'sometimes|integer|min:1', // Page number must be a positive integer
                'length' => 'sometimes|integer|min:1|max:500', // Length must be between 1 and 500
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 400
                ];
            }

            $columns = [
                'company_id',
                'companies.created_at',
                'vat_id',
                'invoice_email',
                'company_phone',
                'zipcode',
                'city',
                'country',
                'companies.id',
                'company_name',
                'is_blocked',
                'migrate_status'
            ];

            // Initialize query
            $dbdata = Company::query()->select($columns);

            // Apply search filter if search value is provided
            if (!empty($request->input('search'))) {
                $sSearch = $request->input('search');
                $dbdata->where(function ($q) use ($sSearch, $columns) {
                    foreach ($columns as $key => $value) {
                        if ($key == 0) {
                            $q->where($value, 'like', '%' . $sSearch . '%');
                        } else {
                            $q->orWhere($value, 'like', '%' . $sSearch . '%');
                        }
                    }
                });
            }

            $page = $request->input('page') ?: '1';
            $perPage = $request->input('length') ?: env("TABLE_LIST_LENGTH");

            $order = $request->input('order_column') ?: 'companies.created_at';
            $orderDirection = $request->input('order_dir') ?: 'desc';

            // Apply pagination and ordering
            $dbdata = $dbdata
            ->orderBy($order, $orderDirection)
            ->paginate($perPage, ['*'], 'page', $page);


            $data = $dbdata->map(function ($item) {
                $item->encryptedId = Crypt::encrypt($item->id);
                return $item;
            });

            // $data = $dbdata->getCollection()->transform(function ($item) {
            //     $item->encryptedId = Crypt::encrypt($item->id);
            //     return $item;
            // });
            // $dbdata->setCollection($data);

            $success['list'] = $data;
            $success['currentPage'] = $dbdata->currentPage();
            $success['totalPages'] =  $dbdata->lastPage();
            $success['recordsTotal'] = $dbdata->total();

            return [
                'status'     => true,
                'message'    => 'Company list',
                'data'       => $success,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while listing the company',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }
    }

    /**
     * Store a newly created company in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function companyCreate(Request $request)
    {
        try {

            // Validate the request
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|unique:companies,company_name',
                'invoice_email' => 'required|email|unique:companies,invoice_email',
                // 'company_phone' => 'required',
                'company_id' => 'required|unique:companies,company_id',
                'company_logo' => 'nullable|file|mimetypes:image/jpeg,image/png,image/svg+xml|dimensions:min_width=500,min_height=500',
                'company_banner' => 'nullable|file|mimetypes:image/jpeg,image/webp|dimensions:min_width=1920,min_height=1080',
            ], [
                'company_name.required' => 'Company name is required',
                'company_name.unique' => 'Company Name already exists',
                'invoice_email.required' => 'Invoice email is required',
                'invoice_email.email' => 'Must be a valid email address',
                'invoice_email.unique' => 'Email already exists',
                // 'company_phone.required' => 'Company phone is required',
                'company_id.required' => 'Company ID is required',
                'company_id.unique' => 'Company ID already exists',
                'company_logo.mimetypes' => 'Only JPEG, PNG, or SVG files are allowed',
                'company_logo.dimensions' => 'The company logo must be at least 500px by 500px.',
                'company_banner.mimetypes' => 'Only JPEG, or WebP files are allowed',
                'company_banner.dimensions' => 'The company banner must be at least 1920px by 1080px.',

            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $apiKey = Str::random(20);
            $apiSecret = Str::random(32);
            $defaultLogoPath = '/assets/img/default-logo.png';
            $defaultBannerPath = '/assets/img/default-banner.jpg';
            $domainName = strtolower(trim($request->company_name));
            $domainName = preg_replace('/[^a-z0-9]+/', '-', $domainName);
            $domainName = trim($domainName, '-');
            $domainName .= ".org";

            // Save the company data
            $company = Company::create([
                'company_id' => $request->company_id,
                'company_name' => $request->company_name,
                'vat_id' => $request->vat_id,
                'invoice_email' => $request->invoice_email,
                'company_phone' => $request->company_phone,
                'zipcode' => $request->zipcode,
                'city' => $request->city,
                'country' => $request->country,
                'ean_number' => $request->ean_number,
                'address' => $request->address,
                'description' => $request->description,
                'company_logo' => $companyLogoPath ?? $defaultLogoPath,
                'company_banner' => $companyBannerPath ?? $defaultBannerPath,
                'apikey' => $apiKey,
                'apisecret' => $apiSecret,
                'domain_name' => $domainName,
                'is_blocked' => $request->is_blocked,
                'mail_config' => 0
            ]);

            // Handle file uploads
            $this->CompanyFileUploadService->handleFileUploads($request, $company->id);

            // Dispatch the job to create the database
            // CreateCompanyDatabaseJob::dispatch($company->id, $company->company_name);
            $this->insertCompanyDatabaseDetails($company->id, $company->company_name);

            return [
                'status'     => true,
                'message'    => 'Company Created Successfully !',
                'data'       => null,
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
    /**
     * Display a  company Details.
     *
     * @param    $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyView($id)
    {
        try {
            // $id = crypt::decrypt($id);
            $id = $id;
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:companies,id',
            ], [
                'id.required' => 'Company id is required',
                'id.exists' => 'Company does not exists',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $data = Company::find($id);
            if (!$data) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => array("company does not exist")],
                    'statusCode' => 422
                ];
            }
            return [
                'status'     => true,
                'message'    => 'company data',
                'data'       => $data,
                'statusCode' => 200
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

    /**
     * Remove the company.
     *
     * @param   $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyDelete($id)
    {
        try {
            // $id = crypt::decrypt($id);
            $id = $id;
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:companies,id',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $company = Company::find($id);

            if (!$company) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => ["Company does not exist"]],
                    'statusCode' => 404
                ];
            }

            // Delete the company
            $company->delete();
            return [
                'status'     => true,
                'message'    => 'Company deleted successfully.',
                'data'       => null,
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

    /**
     * Update the company details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $id is the encrpt 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyUpdate($id, Request $request)
    {

        try {
            // $id = crypt::decrypt($id);
            $id = $id;

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|unique:companies,company_name,' . $id, // Ignore if editing the same company
                'invoice_email' => 'required|email|unique:companies,invoice_email,' . $id, // Ignore if editing the same company
                // 'company_phone' => 'required',
                'company_logo' => 'sometimes|nullable|file|mimetypes:image/jpeg,image/png,image/svg+xml,image/webp|dimensions:min_width=500,min_height=500',
                'company_banner' => 'sometimes|nullable|file|mimetypes:image/jpeg,image/png,image/svg+xml,image/webp|dimensions:min_width=1920,min_height=1080',
            ], [
                'company_name.required' => 'Company name is required',
                'company_name.unique' => 'The company name has already been taken.',
                'invoice_email.required' => 'Invoice email is required',
                'invoice_email.email' => 'Must be a valid email address',
                'invoice_email.unique' => 'The invoice email has already been taken.',
                // 'company_phone.required' => 'Company phone is required',
                'company_logo.mimetypes' => 'Only JPEG, PNG, SVG, or WebP files are allowed',
                'company_logo.dimensions' => 'The company logo must be at least 500px by 500px.',
                'company_banner.mimetypes' => 'Only JPEG, PNG, SVG, or WebP files are allowed',
                'company_banner.dimensions' => 'The company banner must be at least 1920px by 1080px.',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $companyExist = Company::find($id);

            if (!$companyExist)
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => ["error" => ["Company does not exist"]],
                    'statusCode' => 404
                ];

                $companyId = $id;
                Company::where('id', $companyId)->update([
                    'company_name' => $request->company_name,
                    'vat_id' => $request->vat_id,
                    'invoice_email' => $request->invoice_email,
                    'company_phone' => $request->company_phone,
                    'zipcode' => $request->zipcode,
                    'city' => $request->city,
                    'country' => $request->country,
                    'ean_number' => $request->ean_number,
                    'address' => $request->address,
                    'description' => $request->description,
                    'is_blocked' => $request->is_blocked,
                ]);

            // Handle file uploads
                $this->CompanyFileUploadService->handleFileUploads($request, $companyId);

            //after updation send data 
            // $updatedCompanyData = Company::find($companyId);
                return [
                    'status'     => true,
                    'message'    => 'Company updated successfully.',
                    'data'       => null,
                    'statusCode' => 200
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

    /**
     * Display a latest company data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyLatestData()
    {
        try {

            $lastRecord = Company::select('company_id')->latest()->first();
            return [
                'status'     => true,
                'message'    => 'Company Latest Data.',
                'data'       => $lastRecord,
                'statusCode' => 200
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
    /**
     * Store a newly created company database details in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function insertCompanyDatabaseDetails($companyId,$companyName)
    {
        try {
            $payload = json_encode([
                'company_id'   => $companyId,
                'company_name' => $companyName
            ]);

            $otherJobs = OtherJobs::create([
                'company_id' => $companyId,
                'payload'    => $payload, 
                'type'       => 'CompanyDatabaseMigration', 
            ]);

            return [
                'status'     => true,
                'message'    => 'Company database details inserted successfully',
                'data'       => null,
                'statusCode' => 200
            ];

        }
        catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred while insert company database details',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500
            ];
        }

    }
}
