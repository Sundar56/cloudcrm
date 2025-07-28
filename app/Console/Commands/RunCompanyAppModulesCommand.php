<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;


class RunCompanyAppModulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-company-app-modules-command {companyId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * A class property to hold the variable.
     *
     * @var array[]
     */
    protected $apps;


    protected $companyDatabaseService;



    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        parent::__construct();
        $this->companyDatabaseService = $companyDatabaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the company ID from the argument (optional)
        $companyId = $this->argument('companyId');

        $this->apps = [
            [
                'appModule' => [
                    "appname" => "uniconta",
                    "appStatus" => "1",
                ],
                'appVariable' => [
                    [
                        "appvariable" => "userName",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                    [
                        "appvariable" => "password",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                ],
                'appOptions' => [
                    [
                        "appoptionname" => 'User Name',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                    [
                        "appoptionname" => 'Password',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                ]
            ],
            [
                'appModule' => [
                    "appname" => "microsoft",
                    "appStatus" => "1",
                ],
                'appVariable' => [
                    [
                        "appvariable" => "clientId",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                    [
                        "appvariable" => "secretId",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                    [
                        "appvariable" => "redirectUrl",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                    [
                        "appvariable" => "integrateRedirectUrl",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                ],
                'appOptions' => [
                    [
                        "appoptionname" => 'Client Id',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                    [
                        "appoptionname" => 'Secret Id',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                    [
                        "appoptionname" => 'Redirect Url',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                    [
                        "appoptionname" => 'Integrate Redirect Url',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],
                ]
            ],
            [
                'appModule' => [
                    "appname" => "google",
                    "appStatus" => "1",
                ],
                'appVariable' => [
                    [
                        "appvariable" => "googleClientId",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],     
                    [
                        "appvariable" => "googleSecretId",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],
                    [
                        "appvariable" => "googleRedirectUrl",
                        "appvalue" => null,
                        "hidden" => 0,
                    ],         
                ],
                'appOptions' => [
                    [
                        "appoptionname" => 'Google ClientId',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ], 
                     [
                        "appoptionname" => 'Google Secret Id',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ], 
                     [
                        "appoptionname" => 'Google Redirect Url',
                        "appoptiontype" => 'text',
                        "appoptionvalue" => null,
                        "appoptionrequired" => 1,

                    ],               
                ]
            ]
        ];

        if ($companyId) {
            // Run appmodules store for the specified company
            $this->info("Processing appmodules for company ID: $companyId");
            $this->processCompanyAppModules($companyId);
        } else {
            // Run Run appmodules store for all companies
            $this->info('Processing appmodules for all companies...');
            $this->processAllCompanyAppModules();
        }
        return 0; // Success status code
    }

    /**
     * Process appmodules store for a specific company.
     *
     * @param int $companyId
     */
    protected function processCompanyAppModules($companyId)
    {
        $connection = $this->companyDatabaseService->connect($companyId);

        if (!$connection['status']) {
            $this->error('Failed to connect to database: ' . $connection['dbName']);
        }

        $dbname = $connection['dbName'];

        $apps = $this->apps;
        if (!empty($apps)) {
            
            $this->allappications($apps, $dbname);
        }
    }

    /**
     * Process appmodules for all company.
     *
     */
    protected function processAllCompanyAppModules()
    {
        $companies = DB::table('company_databases')->get();

        foreach ($companies as $company) {
            $this->info('Processing company ID: ' . $company->company_id);
            $companyId = $company->company_id;

            $connection = $this->companyDatabaseService->connect($companyId);

            if (!$connection['status']) {
                $this->error('Failed to connect to database: ' . $connection['dbName']);
            }

            $dbname = $connection['dbName'];
            $apps = $this->apps;
            if (!empty($apps)) {

                $this->allappications($apps, $dbname);
            }
        }
    }

    protected function allappications($apps, $dbname)
    {
       
        foreach ($apps as $app) {
           
            $existingRecord = DB::connection($dbname)
                ->table('tbl_appmodules')
                ->where('appname', $app['appModule']['appname'])
                ->first();
            $this->info("Processing app for : " .  $app['appModule']['appname']);

            //insert the app modules
            if ($existingRecord) {
                // Update the existing record and get the ID
                DB::connection($dbname)
                    ->table('tbl_appmodules')
                    ->where('id', $existingRecord->id)
                    ->update([
                        'appstatus' => $app['appModule']['appStatus'],
                        'updated_at' => now(),
                    ]);
                $recordId = $existingRecord->id;
            } else {
                // Insert a new record and get the inserted ID
                $recordId = DB::connection($dbname)
                    ->table('tbl_appmodules')
                    ->insertGetId([
                        'appname' => $app['appModule']['appname'],
                        'appstatus' => $app['appModule']['appStatus'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            $this->info("completed app modules insert: " . $recordId);

             //insert the app variable
            $appVariables = $app['appVariable'];
            foreach ($appVariables as $appVariable) {
                DB::connection($dbname)
                    ->table('tbl_app_variabler')->updateOrInsert(
                        [
                            "appvariable" => $appVariable['appvariable'],
                            "tbl_appmodule_id" => $recordId,
                        ],
                        [
                            "tbl_appmodule_id" =>  $recordId,
                            "appvariable" => $appVariable['appvariable'],
                            "appvalue" => $appVariable['appvalue'],
                            "hidden" => $appVariable['hidden'],
                            'updated_at' => now(),
                            // Use `created_at` only when inserting a new record
                            'created_at' => DB::raw('IFNULL(created_at, NOW())')
                        ]
                    );
            }
            $this->info("completed app variable insert: " . $recordId);


             //insert the app options
            $appOptions = $app['appOptions'];
            foreach ($appOptions as $appOption) {
                DB::connection($dbname)
                    ->table('tbl_appoptions')->updateOrInsert(
                        [
                            "appoptionname" => $appOption['appoptionname'],
                            "appmodule_id" => $recordId,
                        ],
                        [
                            "appmodule_id" => $recordId,
                            "appoptionname" => $appOption['appoptionname'],
                            "appoptiontype" => $appOption['appoptiontype'],
                            "appoptionvalue" => $appOption['appoptionvalue'],
                            "appoptionrequired" => $appOption['appoptionrequired'],
                            'updated_at' => now(),
                            // Use `created_at` only when inserting a new record
                            'created_at' => DB::raw('IFNULL(created_at, NOW())')
                        ]
                    );
            }

            $this->info("completed app options insert: " . $recordId);
        }
    }
}
