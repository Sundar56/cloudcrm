<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Services\CommonService;
use App\Api\Customer\Modules\SocialAccounts\Models\AppVariable;
use App\Api\Customer\Modules\SocialAccounts\Models\AppModule;
use App\Api\Customer\Modules\SocialAccounts\Models\AppOption;
use App\Api\Customer\Modules\SocialAccounts\Models\UnicontaCustomer;
use App\Api\Customer\Modules\SocialAccounts\Models\UnicontaCustomerContact;
use App\Api\Customer\Modules\SocialAccounts\Models\UnicontaCustomerAddress;


class UnicontaService
{
    protected $commonService;
    protected $provider;
    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
        $this->provider = 'uniconta';
    }

    /**
     * Handle the uniconta login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userName' => 'required',
                'password' => 'required',

            ], [
                'userName.required'  => 'email is required',
                'password.required'  => 'password is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $appModule = AppModule::on($dbName)->where([['appname', $this->provider], ['appstatus', 1]])->first();
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;
            AppVariable::on($dbName)
                ->where('tbl_appmodule_id', $appId)
                ->where('appvariable', 'userName')
                ->update(['appvalue' => $request->userName]);

            // dd($password);
            AppVariable::on($dbName)
                ->where('tbl_appmodule_id', $appId)
                ->where('appvariable', 'password')
                ->update(['appvalue' => Crypt::encrypt($request->password)]);

            return [
                'status'     => true,
                'message'    => 'Success.',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get uniconta login details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuth(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $appModule = $this->checkAppExisting($dbName);
            if ($appModule) {
                $appVariable = AppVariable::on($dbName)->where(
                    [
                        ['tbl_appmodule_id', $appModule->id]
                    ]
                )->get();

                $appVariable[1]['appvalue'] = Crypt::decrypt($appVariable[1]['appvalue']);
                
                return [
                    'status'     => true,
                    'message'    => 'Uniconta data',
                    'data'       => $appVariable,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta app missing or disabled',
                    'errors'     => ['error' => 'Uniconta app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get uniconta all customers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomers(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $url = env('UNICONTA_GET_CUSTOMER_URL');
            $response = Http::withBasicAuth($username, $password)->get($url);
            if ($response->successful()) {
                $customers = $response->json()['value'];
                $this->syncCustomers($customers, $dbName, $companyId);

                $rowIds = collect($customers)->pluck('RowId')->toArray();
                UnicontaCustomer::on($dbName)
                    ->whereNotIn('row_id', $rowIds)
                    ->update(['is_deleted' => 1]);

                $search = $request->input('search', '');  // Search term
                $orderBy = $request->input('orderBy') ?: 'id'; // Default order column               
                $orderDir = $request->input('orderDir') ?: 'asc';; // Default order direction              
                $perPage =  $request->input('perPage') ?: env("TABLE_LIST_LENGTH"); // Default page length
                $page =  $request->input('page') ?: '1'; // Default page is 1 if not provided

                // Calculate pagination offset
                $start = ($page - 1) * $perPage;
                // Build query
                $query = UnicontaCustomer::on($dbName)
                    ->where([['company_id', $companyId], ['is_deleted', 0]]);

                // Full table search (search in multiple columns)
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('account', 'LIKE', "%{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%")
                            ->orWhere('zipcode', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%")
                            ->orWhere('contact_email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
                }

                // Get total count before pagination
                $totalData = $query->count();

                // Apply ordering
                $query->orderBy($orderBy, $orderDir);

                // Apply pagination
                $unicontaCustomers = $query->offset($start)
                    ->limit($perPage)
                    ->get();

                // Set totalFiltered after filtering (if applicable)
                $totalFiltered = !empty($search) ? $query->count() : $totalData;


                $response = [
                    'data' => $unicontaCustomers,
                    'total' => $totalData,
                    'filtered' => $totalFiltered,
                    'page' => $page, // Current page
                    'perPage' => $perPage, // Items per page
                ];

                return [
                    'status'     => true,
                    'message'    => 'Uniconta Customers.',
                    'data'       => $response,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to fetch data',
                    'errors'     =>  ["error" => array("Failed to fetch data.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }


    /**
     * Get uniconta customer.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomer($account, Request $request)
    {
        try {

            $dbName = $request->get('dbName');
            $companyId = $request->get('companyId');
            $customer = UnicontaCustomer::on($dbName)->where(
                [
                    ['account', $account],
                    ['company_id', $companyId]
                ]
            )->first();

            if (!$customer)
                return [
                    'status'     => false,
                    'message'    => 'Error Invalid Account.',
                    'errors'       => ['error' => array('Account is Invalid')],
                    'statusCode' => 400
                ];

            return [
                'status'     => true,
                'message'    => 'uniconta customer.',
                'data'       => $customer,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * update uniconta customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomer(Request $request)
    {

        try {

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $validator = Validator::make($request->all(), [
                'RowId' => 'required',
                'Account' => 'required',
                'Name' => 'required',

            ], [
                'RowId.required'  => 'RowId is required',
                'Account.required'  => 'Account is required',
                'Name.required'  => 'Name is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $unicontaFindCustomerUrl = env('UNICONTA_FIND_CUSTOMER_URL');
            $RowId = $request->RowId;
            $response = Http::withBasicAuth($username, $password)->get($unicontaFindCustomerUrl . " " . $RowId);

            if ($response->successful()) {
                $unicontaData = $response->json()['value'];
                if (!empty($unicontaData)) {
                    $unicontaResult = $unicontaData[0];
                    // dd($unicontaResult);
                    $unicontaResult['Group'] = $request->Group;
                    $unicontaResult['RowId'] = $request->RowId;
                    $unicontaResult['Account'] = $request->Account;
                    $unicontaResult['Name'] = $request->Name;
                    $unicontaResult['Address1'] = $request->Address1;
                    $unicontaResult['City'] = $request->City;
                    $unicontaResult['ZipCode'] = $request->ZipCode;
                    $unicontaResult['Country'] = $request->Country;
                    $unicontaResult['CompanyRegNo'] = $request->CompanyRegNo;
                    $unicontaResult['Currency'] = $request->Currency;
                    $unicontaResult['Payment'] = $request->Payment;
                    $unicontaResult['Phone'] = $request->phone;
                    $unicontaResult['Vat'] = $request->Vat;
                    $unicontaResult['VatZone'] = $request->VatZone;
                    $unicontaResult['PriceGroup'] = $request->PriceGroup;
                    $unicontaResult['PostingAccount'] = $request->PostingAccount;
                    $unicontaResult['Dimension1'] = $request->Dimension1;
                    $unicontaResult['UserLanguage'] = $request->UserLanguage;
                    $unicontaResult['ContactEmail'] = $request->ContactEmail;
                    $unicontaResult['InvoiceEmail'] = $request->InvoiceEmail;
                    $unicontaResult['EAN'] = $request->EAN;

                    $updateResponse = Http::withBasicAuth($username, $password)->put(env('UNICONTA_UPDATE_CUSTOMER_URL'), $unicontaResult);
                    if ($updateResponse->successful()) {
                        $customer =   UnicontaCustomer::on($dbName)->updateOrInsert(
                            ['row_id' => $updateResponse['RowId']],
                            [
                                'company_id' => $companyId,
                                "account" => $updateResponse['Account'],
                                "name" => $updateResponse['Name'],
                                "address" => $updateResponse['Address1'] ?? null,
                                "city" => $updateResponse['City'] ?? null,
                                "zipcode" => $updateResponse['ZipCode'] ?? null,
                                "country" => $updateResponse['Country'] ?? null,
                                "company_reg_no" => $updateResponse['CompanyRegNo'] ?? null,
                                "currency" => $updateResponse['Currency'],
                                "phone" => $updateResponse['Phone'] ?? null,
                                "payment" => $updateResponse['Payment'] ?? null,
                                "vat_zone" => $updateResponse['VatZone'] ?? null,
                                "vat_number" => $updateResponse['Vat'] ?? null,
                                "price_group" => $updateResponse['PriceGroup'] ?? null,
                                "posting_account" => $updateResponse['PostingAccount'] ?? null,
                                "dimension1" => $updateResponse['Dimension1'] ?? null,
                                "user_language" => $updateResponse['UserLanguage'] ?? null,
                                "contact_email" => $updateResponse['ContactEmail'] ?? null,
                                "invoice_email" => $updateResponse['InvoiceEmail'] ?? null,
                                "ean" => $updateResponse['EAN'] ?? null,
                                "group" => $updateResponse['Group'] ?? null,
                                'updated_at' => now(),
                            ]

                        );

                        return [
                            'status'     => true,
                            'message'    => 'Update Customer successfully!',
                            'data'       => null,
                            'statusCode' => 200
                        ];
                    } else {
                        return [
                            'status'     => false,
                            'message'    => 'Uniconta update error',
                            'errors'     =>  ["error" => array("Uniconta update error.")],
                            'statusCode' => $updateResponse->status()
                        ];
                    }
                } else {
                    return [
                        'status'     => false,
                        'message'    => 'Invalid Row ID',
                        'errors'     =>  ["error" => array("Invalid Row ID'")],
                        'statusCode' => 400
                    ];
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta update error',
                    'errors'     =>  ["error" => array("Uniconta update error.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Handle the store uniconta customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCustomer(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $validator = Validator::make($request->all(), [
                'Account' => 'required',
                'Name' => 'required',

            ], [
                'Account.required'  => 'Account is required',
                'Name.required'  => 'Name is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $payload = [
                'Account' => $request->Account,
                'Name' => $request->Name ?? null,
                'Address1' =>  $request->Address1 ?? null,
                'City' => $request->City ?? null,
                'ZipCode' =>  $request->ZipCode ?? null,
                'Country' =>  $request->Country ?? null,
                'CompanyRegNo' =>  $request->CompanyRegNo ?? null,
                'Phone' =>  $request->Phone ?? null,
                'UserLanguage' =>  $request->UserLanguage ?? null,
                'ContactEmail' =>  $request->ContactEmail ?? null,
                'Vat' =>  $request->Vat ?? null,
                'InvoiceEmail' =>  $request->InvoiceEmail ?? null,
                'Dimension1' =>  $request->Dimension1 ?? null,
                'Payment' =>  $request->Payment ?? null,
                'VatZone' =>  $request->VatZone ?? null,
                'EAN' =>  $request->EAN ?? null,
                'PostingAccount' =>  $request->PostingAccount ?? null,
                'Currency' =>  $request->Currency ?? null,
                'Group' =>  $request->Group ?? null,
                'PriceGroup' =>  $request->PriceGroup ?? null,
            ];
            $createResponse = Http::withBasicAuth($username, $password)->post(env('UNICONTA_STORE_CUSTOMER_URL'), $payload);
            if ($createResponse->successful()) {
                // dd($createResponse->json());
                $updateResponse = $createResponse->json();

                $customer =   UnicontaCustomer::on($dbName)->updateOrInsert(
                    ['row_id' => $updateResponse['RowId']],
                    [
                        'company_id' => $companyId,
                        "account" => $updateResponse['Account'],
                        "name" => $updateResponse['Name'],
                        "address" => $updateResponse['Address1'] ?? null,
                        "city" => $updateResponse['City'] ?? null,
                        "zipcode" => $updateResponse['ZipCode'] ?? null,
                        "country" => $updateResponse['Country'] ?? null,
                        "company_reg_no" => $updateResponse['CompanyRegNo'] ?? null,
                        "currency" => $updateResponse['Currency'],
                        "phone" => $updateResponse['Phone'] ?? null,
                        "payment" => $updateResponse['Payment'] ?? null,
                        "vat_zone" => $updateResponse['VatZone'] ?? null,
                        "vat_number" => $updateResponse['Vat'] ?? null,
                        "price_group" => $updateResponse['PriceGroup'] ?? null,
                        "posting_account" => $updateResponse['PostingAccount'] ?? null,
                        "dimension1" => $updateResponse['Dimension1'] ?? null,
                        "user_language" => $updateResponse['UserLanguage'] ?? 'Default',
                        "contact_email" => $updateResponse['ContactEmail'] ?? null,
                        "invoice_email" => $updateResponse['InvoiceEmail'] ?? null,
                        "ean" => $updateResponse['EAN'] ?? null,
                        "group" => $updateResponse['Group'] ?? null,
                        'created_by' => 1,
                        'created_at' => now(),
                    ]
                );
                return [
                    'status'     => true,
                    'message'    => 'Create Customer successfully!',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => $createResponse->json(),
                    'errors'     =>  ["error" => array("Uniconta customer creation failed.")],
                    'statusCode' => $createResponse->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Delete uniconta customer.
     *
     * @param   uniconta customer $id and account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCustomer(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $validator = Validator::make($request->all(), [
                'Account' => 'required',
                'rowId' => 'required',

            ], [
                'Account.required'  => 'Account is required',
                'rowId.required'  => 'rowId is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $id = $request->rowId;
            $payload = [
                "Account" => $request->Account,
            ];
            $response = Http::withBasicAuth($username, $password)->withHeaders([
                'Accept' => 'application/json',
            ])->delete(env('UNICONTA_DELETE_CUSTOMER_URL') . $id, $payload);
            if ($response->successful()) {
                $deleteEvent = UnicontaCustomer::on($dbName)
                    ->where('row_id', $id)
                    ->delete();
                return [
                    'status'     => true,
                    'message'    => 'customer deleted successfully!',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to delete the customer',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' =>  $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }


    public function syncCustomers($customers, $dbName, $companyId)
    {
        $batchSize = 100;
        $batchData = [];
        foreach ($customers as $customer) {
            $batchData[] = [
                'company_id' => $companyId,
                'account' => $customer['Account'],
                'row_id' => $customer['RowId'],
                'name' => $customer['Name'] ?? null,
                'address' => $customer['Address1'] ?? null,
                'city' => $customer['City'] ?? null,
                'zipcode' => $customer['ZipCode'] ?? null,
                'country' => $customer['Country'] ?? null,
                'company_reg_no' => $customer['CompanyRegNo'] ?? null,
                'phone' => $customer['Phone'] ?? null,
                'user_language' => $customer['UserLanguage'] ?? null,
                'contact_email' => $customer['ContactEmail'] ?? null,
                'vat_number' => $customer['Vat'] ?? null,
                'invoice_email' => $customer['InvoiceEmail'] ?? null,
                'dimension1' => $customer['Dimension1'] ?? null,
                'payment' => $customer['Payment'] ?? null,
                'vat_zone' => $customer['VatZone'] ?? null,
                'ean' => $customer['EAN'] ?? null,
                'posting_account' => $customer['PostingAccount'] ?? null,
                'currency' => $customer['Currency'] ?? null,
                'group' => $customer['Group'] ?? null,
                'price_group' => $customer['PriceGroup'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batchData) >= $batchSize) {
                // Insert batch data into the database
                UnicontaCustomer::on($dbName)->upsert(
                    $batchData,
                    ['row_id'],  // Unique identifier column(s) for update
                    ['company_id', 'account', 'name', 'address', 'city', 'zipcode', 'country', 'company_reg_no', 'phone', 'user_language', 'contact_email', 'vat_number', 'invoice_email', 'dimension1', 'payment', 'vat_zone', 'ean', 'posting_account', 'currency', 'group', 'price_group', 'updated_at']  // Columns to update if row_id exists
                );

                $batchData = [];
            }
        }

        // Insert any remaining data that didn't fill a complete batch
        if (!empty($batchData)) {
            UnicontaCustomer::on($dbName)->upsert(
                $batchData,
                ['row_id'],
                ['company_id', 'account', 'name', 'address', 'city', 'zipcode', 'country', 'company_reg_no', 'phone', 'user_language', 'contact_email', 'vat_number', 'invoice_email', 'dimension1', 'payment', 'vat_zone', 'ean', 'posting_account', 'currency', 'group', 'price_group', 'updated_at']
            );
        }
    }

    /**
     * CheckApp existing in tbl_appmodules table
     */
    public function checkAppExisting($dbName)
    {
        return AppModule::on($dbName)->where([['appname', $this->provider], ['appstatus', 1]])->first();
    }

    /**
     * tbl_app_variabler table - get value using variable name
     */
    private function getAppVariableValue($dbName, $appId, $variableName)
    {
        $variable = AppVariable::on($dbName)
            ->where('tbl_appmodule_id', $appId)
            ->where('appvariable', $variableName)
            ->first();

        return $variable ? $variable->appvalue : null;
    }

    public function staticData(Request $request)
    {
        $response = config('app.uniconta_static_data');
        return [
            'status'     => true,
            'message'    => 'selects',
            'data'       => $response,
            'statusCode' => 200
        ];
    }

    /**
     * check uniconta connet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkconnect(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;
            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');
            if ((!empty($username) && !empty($password))) {
                return [
                    'status'     => true,
                    'message'    => 'Uniconta connection',
                    'data'       =>  [
                        'status' => true,
                        'service' => 'Uniconta',
                        'message' => 'Uniconta account is successfully connected'
                    ],
                    'statusCode' => 200
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Uniconta connection',
                'data'       =>  [
                    'status' => false,
                    'service' => 'Uniconta',
                    'message' => 'Uniconta account did not connect'
                ],
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * get uniconta select option.
     *
     *  @param   select menu name
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectOption(Request $request)
    {
        try {
            $view = $request->view;
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            // $url = "https://odata.uniconta.com/odata/PaymentTermClient";
            // $response = Http::withBasicAuth($username, $password)->get($url);
            $response = $this->selectMenuList($view, $username, $password);
            if ($response->status() === 200) {
                $customersPaymentList = $response->json()['value'];
                return [
                    'status'     => true,
                    'message'    => 'Uniconta Customers.',
                    'data'       => $customersPaymentList,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to fetch data',
                    'errors'     =>  ["error" => array("Failed to fetch data.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function selectMenuList($view, $username, $password)
    {
        $url = '';

        switch ($view) {
            case 'paymentList':
                $url = env('UNICONTA_CUSTOMER_PAYMENT_URL');
                break;
            case 'revList':
                $url = env('UNICONTA_CUSTOMER_REVENUE_URL');
                break;
            case 'group':
                $url = env('UNICONTA_CUSTOMER_DEBTOR_GROUP_URL');
                break;
            default:
                return response()->json(['error' => 'Invalid view type'], 400);
        }

        $response = Http::withBasicAuth($username, $password)->get($url);

        return $response;
    }

    /**
     * Handle the store uniconta customer contact.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeContact(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'reference_id' => 'required',
                'contact_type' => 'required',
                'customer_account' => 'required',
            ], [
                'name.required'  => 'Name is required',
                'reference_id.required'  => 'Refernce ID is required',
                'contact_type.required'  => 'Contact Type is required',
                'customer_account.required'  => 'Customer account is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $unicontaFindContact = env('UNICONTA_FIND_CONTACT_URL');
            $refernce_id = $request->reference_id;
            // dd($refernce_id);
            // $response = Http::withBasicAuth($username, $password)->get('https://odata.uniconta.com/odata/ContactClient?$filter=ReferenceId eq '".$refernceId."'');
            // $response = Http::withBasicAuth($username, $password)
            //     ->get('https://odata.uniconta.com/odata/ContactClient?$filter=ReferenceId eq ' ." '" . 123321 . "'");
            $response = Http::withBasicAuth($username, $password)->get($unicontaFindContact . " '" . $refernce_id . "'");

            if ($response->status() == 200) {
                $responseData = $response->json();
                //check data already exist based on refernce id
                if (!empty($responseData['value']) && count($responseData['value']) > 0) {
                    return [
                        'status'     => false,
                        'message'    => 'Data already exist !!',
                        'errors'     => ['error' => 'Data already exist !!'],
                        'statusCode' => 400,
                    ];
                } else {
                    $payload = [
                        "DCAccount" => $request->customer_account,
                        "ReferenceId" =>  $request->reference_id,
                        "Name" => $request->name,
                        "Mobile" =>  $request->phone,
                        "Email" =>  $request->email,
                        "Title" =>  $request->contact_type,
                        "ContactType" => $request->contact_type,
                    ];

                    $createResponse = Http::withBasicAuth($username, $password)->post(env('UNICONTA_STORE_CONTACT_URL'), $payload);
                    if ($createResponse->successful()) {
                        $updateResponse = $createResponse->json();
                        $conatact =   UnicontaCustomerContact::on($dbName)->insert(
                            [
                                'row_id' => $updateResponse['RowId'],
                                "tbl_uniconta_customer_account" => $updateResponse['DCAccount'],
                                "name" => $updateResponse['Name'],
                                "reference_id" => $updateResponse['ReferenceId'],
                                "email" => $updateResponse['Email'] ?? null,
                                "phone" => $updateResponse['Mobil'] ?? null,
                                "contact_type" => $updateResponse['ContactType'] ?? null,
                                "notes" => $request->notes ?? null,
                                "image_path" => $request->image_path ?? null,
                                'created_at' => now(),
                            ]
                        );
                        return [
                            'status'     => true,
                            'message'    => 'Create Contact successfully!',
                            'data'       => null,
                            'statusCode' => 200
                        ];
                    } else {
                        return [
                            'status'     => false,
                            'message'    => $createResponse->json(),
                            'errors'     =>  ["error" => array("Uniconta contact creation failed.")],
                            'statusCode' => $createResponse->status()
                        ];
                    }
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta insert error.',
                    'errors'     => ['error' => 'Uniconta insert error.'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * update customer Contact.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateContact(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'reference_id' => 'required',
                'contact_type' => 'required',
                'customer_account' => 'required',
            ], [
                'name.required'  => 'Name is required',
                'reference_id.required'  => 'Refernce ID is required',
                'contact_type.required'  => 'Contact Type is required',
                'customer_account.required'  => 'Customer account is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $unicontaFindContactUrl = env('UNICONTA_FIND_CONTACT_WITHROWID_URL');
            $RowId = $request->row_id;
            $response = Http::withBasicAuth($username, $password)->get($unicontaFindContactUrl . " " . $RowId);

            if ($response->successful()) {
                $unicontaData = $response->json()['value'];
                if (!empty($unicontaData) && count($response->json()['value']) == 1) {
                    $unicontaResult = $unicontaData[0];
                    $unicontaResult['Name'] = $request->name;
                    $unicontaResult['ReferenceId'] = $request->reference_id;
                    $unicontaResult['ContactType'] = $request->contact_type;
                    $unicontaResult['Email'] = $request->email;
                    $unicontaResult['Mobil'] = $request->phone;
                    $unicontaResult['DCAccount'] = $request->customer_account;
                    $unicontaResult['Title'] = $request->ContactType;
                    $response = Http::withBasicAuth($username, $password)->put(env('UNICONTA_UPDATE_CONTACT_URL'), $unicontaResult);
                    if ($response->successful()) {

                        $updateResponse = $response->json();
                        // dd( $updateResponse['RowId']);
                        UnicontaCustomerContact::on($dbName)->where([['id', $request->contact_id], ['row_id', $request->row_id]])->update([
                            'row_id' => $updateResponse['RowId'],
                            "tbl_uniconta_customer_account" => $updateResponse['DCAccount'],
                            "name" => $updateResponse['Name'],
                            "reference_id" => $updateResponse['ReferenceId'],
                            "email" => $updateResponse['Email'] ?? null,
                            "phone" => $updateResponse['Mobil'] ?? null,
                            "contact_type" => $updateResponse['ContactType'] ?? null,
                            "notes" => $request->notes ?? null,
                            "image_path" => $request->image_path ?? null,
                            'updated_at' => now(),
                        ]);
                        return [
                            'status'     => true,
                            'message'    => 'Update Contact successfully!',
                            'data'       => null,
                            'statusCode' => 200
                        ];
                    } else {
                        return [
                            'status'     => false,
                            'message'    => 'Uniconta update error',
                            'errors'     =>  ["error" => array("Uniconta update error.")],
                            'statusCode' => $response->status()
                        ];
                    }
                } else {
                    return [
                        'status'     => false,
                        'message'    => 'Invalid Row ID',
                        'errors'     =>  ["error" => array("Invalid Row ID'")],
                        'statusCode' => 400
                    ];
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta update error',
                    'errors'     =>  ["error" => array("Uniconta update error.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get uniconta contact.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContact($id, Request $request)
    {
        try {

            $dbName = $request->get('dbName');
            $companyId = $request->get('companyId');
            $contact = UnicontaCustomerContact::on($dbName)->where(
                [
                    ['id', $id],
                ]
            )->first();

            if (!$contact)
                return [
                    'status'     => false,
                    'message'    => 'Error Invalid Account.',
                    'errors'       => ['error' => array('Account is Invalid')],
                    'statusCode' => 400
                ];

            return [
                'status'     => true,
                'message'    => 'Uniconta Contact.',
                'data'       => $contact,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get uniconta contact.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContacts($account, Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $url = env('UNICONTA_GET_CONTACTS_URL');
            $response = Http::withBasicAuth($username, $password)->get($url);
            if ($response->successful()) {
                $customers = $response->json()['value'];
                // dd($customers);
                if (!empty($customers)) {
                    // Filter the records where DCAccount matches $account
                    $filteredRecords = array_filter($customers, function ($customers) use ($account) {
                        return $customers['DCAccount'] === $account;
                    });
                }
                $this->syncContact($filteredRecords, $dbName);

                $rowIds = collect($filteredRecords)->pluck('RowId')->toArray();
                UnicontaCustomerContact::on($dbName)
                    ->whereNotIn('row_id', $rowIds)
                    ->update(['is_deleted' => 1]);

                $search = $request->input('search', '');  // Search term
                $orderBy = $request->input('orderBy') ?: 'id'; // Default order column               
                $orderDir = $request->input('orderDir') ?: 'asc';; // Default order direction              
                $perPage =  $request->input('perPage') ?: env("TABLE_LIST_LENGTH"); // Default page length
                $page =  $request->input('page') ?: '1'; // Default page is 1 if not provided

                // Calculate pagination offset
                $start = ($page - 1) * $perPage;

                $query = UnicontaCustomerContact::on($dbName)->where(
                    [['tbl_uniconta_customer_account', $account], ['is_deleted', 0]]
                );

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('reference_id', 'LIKE', "%{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%")
                            ->orWhere('contact_type', 'LIKE', "%{$search}%");
                    });
                }

                // Get total count before pagination
                $totalData = $query->count();

                // Apply ordering
                $query->orderBy($orderBy, $orderDir);

                // Apply pagination
                $unicontaContacts = $query->offset($start)
                    ->limit($perPage)
                    ->get();

                $totalFiltered = !empty($search) ? $query->count() : $totalData;


                $response = [
                    'data' => $unicontaContacts,
                    'total' => $totalData,
                    'filtered' => $totalFiltered,
                    'page' => $page, // Current page
                    'perPage' => $perPage, // Items per page
                ];
                // $unicontaContacts = DB::connection($dbName)->table('tbl_uniconta_customer_contacts')->where(
                //     ['tbl_uniconta_customer_account' => $account]
                // )->get();
                return [
                    'status'     => true,
                    'message'    => 'Uniconta Contacts.',
                    'data'       => $response,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to fetch data',
                    'errors'     =>  ["error" => array("Failed to fetch data.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function syncContact($contacts, $dbName)
    {
        $batchSize = 100;
        $batchData = [];
        foreach ($contacts as $contact) {
            $batchData[] = [
                'row_id' => $contact['RowId'],
                "tbl_uniconta_customer_account" => $contact['DCAccount'],
                "name" => $contact['Name'],
                "reference_id" => $contact['ReferenceId'],
                "email" => $contact['Email'] ?? null,
                "phone" => $contact['Mobil'] ?? null,
                "contact_type" => $contact['ContactType'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batchData) >= $batchSize) {
                // Insert batch data into the database
                UnicontaCustomerContact::on($dbName)->upsert(
                    $batchData,
                    ['row_id'],  // Unique identifier column(s) for update
                    ['tbl_uniconta_customer_account', 'name', 'reference_id', 'email', 'phone', 'contact_type', 'updated_at']  // Columns to update if row_id exists
                );

                $batchData = [];
            }
        }

        // Insert any remaining data that didn't fill a complete batch
        if (!empty($batchData)) {
            UnicontaCustomerContact::on($dbName)->upsert(
                $batchData,
                ['row_id'],
                ['tbl_uniconta_customer_account', 'name', 'reference_id', 'email', 'phone', 'contact_type', 'updated_at']
            );
        }
    }

    /**
     * Delete uniconta customer contact.
     *
     * @param   uniconta contact $row_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteContact(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $validator = Validator::make($request->all(), [
                'rowId' => 'required',

            ], [
                'rowId.required'  => 'rowId is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $id = $request->rowId;
            $payload = [
                'RowId' =>  $id
            ];
            $response = Http::withBasicAuth($username, $password)->withHeaders([
                'Accept' => 'application/json',
            ])->delete(env('UNICONTA_DELETE_CONTACT_URL'), $payload);
            if ($response->successful()) {
                $deleteEvent = UnicontaCustomerContact::on($dbName)
                    ->where('row_id', $id)
                    ->delete();
                return [
                    'status'     => true,
                    'message'    => 'Contact deleted successfully!',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to delete the Contact',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' =>  $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }


    /**
     * Get uniconta address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress($account, Request $request)
    {
        try {

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $url = env('UNICONTA_LIST_ADDRESS_URL');
            $response = Http::withBasicAuth($username, $password)->get($url);
            if ($response->successful()) {
                $customers = $response->json()['value'];
                // dd($customers);
                if (!empty($customers)) {
                    // Filter the records where DCAccount matches $account
                    $filteredRecords = array_filter($customers, function ($customers) use ($account) {
                        return $customers['DCAccount'] === $account;
                    });
                }
                $this->syncaddress($filteredRecords, $dbName);

                $rowIds = collect($filteredRecords)->pluck('RowId')->toArray();
                UnicontaCustomerAddress::on($dbName)
                    ->whereNotIn('row_id', $rowIds)
                    ->update(['is_deleted' => 1]);

                $search = $request->input('search', '');  // Search term
                $orderBy = $request->input('orderBy') ?: 'id'; // Default order column               
                $orderDir = $request->input('orderDir') ?: 'asc';; // Default order direction              
                $perPage =  $request->input('perPage') ?: env("TABLE_LIST_LENGTH"); // Default page length
                $page =  $request->input('page') ?: '1'; // Default page is 1 if not provided
                $start = ($page - 1) * $perPage;

                $query = UnicontaCustomerAddress::on($dbName)->where(
                    [['tbl_uniconta_customer_account', $account], ['is_deleted', 0]]
                );

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('reference_number', 'LIKE', "%{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('zipcode', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%")
                            ->orWhere('country', 'LIKE', "%{$search}%");
                    });
                }

                // Get total count before pagination
                $totalData = $query->count();

                // Apply ordering
                $query->orderBy($orderBy, $orderDir);

                // Apply pagination
                $unicontaAddress = $query->offset($start)
                    ->limit($perPage)
                    ->get();

                $totalFiltered = !empty($search) ? $query->count() : $totalData;


                $response = [
                    'data' => $unicontaAddress,
                    'total' => $totalData,
                    'filtered' => $totalFiltered,
                    'page' => $page, // Current page
                    'perPage' => $perPage, // Items per page
                ];
                // $unicontaAddress = DB::connection($dbName)->table('tbl_uniconta_delivery_address')->where(
                //     ['tbl_uniconta_customer_account' => $account]
                // )->get();
                return [
                    'status'     => true,
                    'message'    => 'Uniconta delivery address.',
                    'data'       => $response,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to fetch data',
                    'errors'     =>  ["error" => array("Failed to fetch data.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function syncaddress($address, $dbName)
    {
        $batchSize = 100;
        $batchData = [];
        foreach ($address as $addres) {
            $batchData[] = [
                'row_id' => $addres['RowId'],
                "tbl_uniconta_customer_account" => $addres['DCAccount'],
                "name" => $addres['Name'],
                "reference_number" => $addres['Code'],
                "email" => $addres['ContactEmail'] ?? null,
                "phone" => $addres['Phone'] ?? null,
                "country" => $addres['Country'] ?? null,
                "address1" => $addres['Address1'] ?? null,
                "address2" => $addres['Address2'] ?? null,
                "city" => $addres['City'] ?? null,
                "zipcode" => $addres['ZipCode'] ?? null,
                "vat" => $addres['CompanyRegNo'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batchData) >= $batchSize) {
                // Insert batch data into the database
                UnicontaCustomerAddress::on($dbName)->upsert(
                    $batchData,
                    ['row_id'],  // Unique identifier column(s) for update
                    ['tbl_uniconta_customer_account', 'name', 'reference_number', 'email', 'phone', 'country', 'address1', 'address2', 'city', 'zipcode', 'vat', 'updated_at']  // Columns to update if row_id exists
                );

                $batchData = [];
            }
        }

        // Insert any remaining data that didn't fill a complete batch
        if (!empty($batchData)) {
            UnicontaCustomerAddress::on($dbName)->upsert(
                $batchData,
                ['row_id'],
                ['tbl_uniconta_customer_account', 'name', 'reference_number', 'email', 'phone', 'country', 'address1', 'address2', 'city', 'zipcode', 'vat', 'updated_at']
            );
        }
    }

    /**
     * Get uniconta delivery address for single.
     *
     * @param    customer $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeliveryAddress($id, Request $request)
    {
        try {

            $dbName = $request->get('dbName');
            $companyId = $request->get('companyId');
            $address = UnicontaCustomerAddress::on($dbName)->where(
                [
                    ['id', $id],
                ]
            )->first();

            if (!$address)
                return [
                    'status'     => false,
                    'message'    => 'Error Invalid Account.',
                    'errors'       => ['error' => array('Account is Invalid')],
                    'statusCode' => 400
                ];

            return [
                'status'     => true,
                'message'    => 'Uniconta Contact.',
                'data'       => $address,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Handle the store uniconta customer delivery address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'reference_number' => 'required',
                'customer_account' => 'required',
            ], [
                'name.required'  => 'Name is required',
                'reference_number.required'  => 'Refernce Number is required',
                'customer_account.required'  => 'Customer account is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $unicontaFindAddress = env('UNICONTA_FIND_ADDRESS_URL');
            $refernce_number = $request->reference_number;
            $response = Http::withBasicAuth($username, $password)->get($unicontaFindAddress . " '" . $refernce_number . "'");
            // dd($response->json());

            if ($response->status() == 200) {
                $responseData = $response->json();
                //check data already exist based on refernce id
                if (!empty($responseData['value']) && count($responseData['value']) > 0) {
                    return [
                        'status'     => false,
                        'message'    => 'Data already exist !!',
                        'errors'     => ['error' => 'Data already exist !!'],
                        'statusCode' => 400,
                    ];
                } else {
                    $payload = [
                        "DCAccount" => $request->customer_account,
                        "Code" =>  $request->reference_number,
                        "Name" => $request->name,
                        "City" =>  $request->city,
                        "ZipCode" =>  $request->zipcode,
                        "Country" =>  $request->country,
                        "Phone" => $request->phone,
                        "UserLanguage" => "Default",
                        "ContactEmail" => $request->email,
                        "Address1" => $request->address1,
                        "Address2" => $request->address2,
                        "CompanyRegNo" => $request->vat,
                    ];

                    $createResponse = Http::withBasicAuth($username, $password)->post(env('UNICONTA_STORE_ADDRESS_URL'), $payload);
                    if ($createResponse->successful()) {
                        $updateResponse = $createResponse->json();
                        $conatact =   UnicontaCustomerAddress::on($dbName)->insert(
                            [
                                'row_id' => $updateResponse['RowId'],
                                "tbl_uniconta_customer_account" => $updateResponse['DCAccount'],
                                "reference_number" => $updateResponse['Code'],
                                "name" => $updateResponse['Name'],
                                "email" => $updateResponse['ContactEmail'] ?? null,
                                "phone" => $updateResponse['Phone'] ?? null,
                                "country" => $updateResponse['Country'] ?? null,
                                "address1" => $updateResponse['Address1'] ?? null,
                                "address2" => $updateResponse['Address2'] ?? null,
                                "city" => $updateResponse['City'] ?? null,
                                "zipcode" => $updateResponse['ZipCode'] ?? null,
                                "vat" => $updateResponse['CompanyRegNo'] ?? null,
                                "notes" => $request->notes ?? null,
                                "image_path" => $request->image_path ?? null,
                                'created_at' => now(),
                            ]
                        );
                        return [
                            'status'     => true,
                            'message'    => 'Create Delivery Address successfully!',
                            'data'       => null,
                            'statusCode' => 200
                        ];
                    } else {
                        return [
                            'status'     => false,
                            'message'    => $createResponse->json(),
                            'errors'     =>  ["error" => array("Uniconta contact creation failed.")],
                            'statusCode' => $createResponse->status()
                        ];
                    }
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta insert error.',
                    'errors'     => ['error' => 'Uniconta insert error.'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * update Delivery address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'reference_number' => 'required',
                'customer_account' => 'required',
            ], [
                'name.required'  => 'Name is required',
                'reference_number.required'  => 'Refernce Number is required',
                'customer_account.required'  => 'Customer account is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }

            $unicontaFindAddress = env('UNICONTA_FIND_ADDRESS_URL');
            $refernce_number = $request->reference_number;
            $response = Http::withBasicAuth($username, $password)->get($unicontaFindAddress . " '" . $refernce_number . "'");
            // dd($response->json());
            if ($response->successful()) {
                $unicontaData = $response->json()['value'];
                if (!empty($unicontaData) && count($response->json()['value']) == 1) {
                    $unicontaResult = $unicontaData[0];
                    $unicontaResult['Name'] = $request->name;
                    $unicontaResult['Code'] = $request->reference_number;
                    $unicontaResult['DCAccount'] = $request->customer_account;
                    $unicontaResult['City'] = $request->city;
                    $unicontaResult['ZipCode'] = $request->zipcode;
                    $unicontaResult['Country'] = $request->country;
                    $unicontaResult['Phone'] = $request->phone;
                    $unicontaResult['ContactEmail'] = $request->email;
                    $unicontaResult['Address1'] = $request->address1;
                    $unicontaResult['Address2'] = $request->address2;
                    $unicontaResult['CompanyRegNo'] = $request->vat;
                    $response = Http::withBasicAuth($username, $password)->put(env('UNICONTA_UPDATE_ADDRESS_URL'), $unicontaResult);
                    if ($response->successful()) {

                        $updateResponse = $response->json();
                        // dd( $updateResponse['RowId']);
                        $address =   UnicontaCustomerAddress::on($dbName)->where([['id', $request->address_id]])->update(
                            [
                                'row_id' => $updateResponse['RowId'],
                                "tbl_uniconta_customer_account" => $updateResponse['DCAccount'],
                                "reference_number" => $updateResponse['Code'],
                                "name" => $updateResponse['Name'],
                                "email" => $updateResponse['ContactEmail'] ?? null,
                                "phone" => $updateResponse['Phone'] ?? null,
                                "country" => $updateResponse['Country'] ?? null,
                                "address1" => $updateResponse['Address1'] ?? null,
                                "address2" => $updateResponse['Address2'] ?? null,
                                "city" => $updateResponse['City'] ?? null,
                                "zipcode" => $updateResponse['ZipCode'] ?? null,
                                "vat" => $updateResponse['CompanyRegNo'] ?? null,
                                "notes" => $request->notes ?? null,
                                "image_path" => $request->image_path ?? null,
                                'updated_at' => now(),
                            ]
                        );
                        return [
                            'status'     => true,
                            'message'    => 'Update address successfully!',
                            'data'       => null,
                            'statusCode' => 200
                        ];
                    } else {
                        return [
                            'status'     => false,
                            'message'    => 'Uniconta update error',
                            'errors'     =>  ["error" => array("Uniconta update error.")],
                            'statusCode' => $response->status()
                        ];
                    }
                } else {
                    return [
                        'status'     => false,
                        'message'    => 'Invalid Number',
                        'errors'     =>  ["error" => array("Invalid Row ID'")],
                        'statusCode' => 400
                    ];
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta update error',
                    'errors'     =>  ["error" => array("Uniconta update error.")],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }


    /**
     * Delete uniconta customer delivery address.
     *
     * @param   uniconta contact $id and account
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAddress(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $companyId = $request->get('companyId');
            $appModule =  $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Uniconta application not found.',
                    'errors'     => ['error' => 'Uniconta application not found.'],
                    'statusCode' => 500,
                ];
            }
            $appId = $appModule->id;

            $username = $this->getAppVariableValue($dbName, $appId, 'userName');
            $password = $this->getAppVariableValue($dbName, $appId, 'password');

            if ($password) {
                $password = Crypt::decrypt($password);
            }
            $validator = Validator::make($request->all(), [
                'rowId' => 'required',
                'referenceNumber' => 'required',

            ], [
                'rowId.required'  => 'rowId is required',
                'referenceNumber.required'  => 'Reference Number is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $id = $request->rowId;
            $referenceNumber = $request->referenceNumber;
            $payload = [
                'RowId' =>  $id,
                'code'  =>  $referenceNumber
            ];
            // dd($payload);
            $response = Http::withBasicAuth($username, $password)->withHeaders([
                'Accept' => 'application/json',
            ])->delete(env('UNICONTA_DELETE_ADDRESS_URL'), $payload);
            if ($response->successful()) {
                $deleteEvent = UnicontaCustomerAddress::on($dbName)
                    ->where('row_id', $id)
                    ->delete();
                return [
                    'status'     => true,
                    'message'    => 'Deleted successfully!',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to delete the Address',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' =>  $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
