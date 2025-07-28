<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;

class InsertUsersToCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:users {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert predefined users to companies database';

    // To keep track of which company IDs have already been processed
    protected $processedCompanies = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        // Fetch all companies
        // If company_id is passed, we will fetch the specific company; otherwise, we will process all companies
        if ($companyId) {
            $companies = CompanyDatabase::where('company_id', $companyId)->get(); // Fetch only the company with the provided company_id
        } else {
            $companies = CompanyDatabase::all(); // Fetch all companies if no company_id is provided
        }

        // Define the users to be inserted
        $users = [
            [
                'brugernavn' => 'sagsformular',
                'password' => '',
                'userlevel' => '2',
                'lastlogin' => '',
                'siteaccess' => '1-1-1-1',
                'navn' => 'Online formular',
                'oensker_email_ved_specifik_sag' => '1',
                'email' => 'system@cloud-crm.dk',
                'usynlig' => '0',
                'hideuser' => '1',
            ],
            [
                'brugernavn' => 'Securenext',
                'password' => '$P$BDMId6WmVcuYPxaAYRW/J4tlkf6Hv5.',
                'userlevel' => '2',
                'lastlogin' => '06-11-2024 13:00:35',
                'siteaccess' => '1-1-1-1',
                'navn' => 'Securenext',
                'oensker_email_ved_specifik_sag' => '1',
                'email' => 'system@cloud-crm.dk',
                'usynlig' => '0',
                'hideuser' => '0',
            ],
            [
                'brugernavn' => 'shankar@cloud-crm.dk',
                'password' => '$P$B.nPHTzirpLd74wwUsl..mxOlUMybo1',
                'userlevel' => '2',
                'lastlogin' => '18-10-2024 6:39:28',
                'siteaccess' => '1-1-1-1',
                'navn' => 'Shankar Jeyakumar',
                'oensker_email_ved_specifik_sag' => '1',
                'email' => 'shankar@cloud-crm.dk',
                'usynlig' => '0',
                'hideuser' => '0',
            ],
            [
                'brugernavn' => 'shankardk',
                'password' => '$P$BlvywrcqS6Z/URXPrYU8IS4nBcmA0B.',
                'userlevel' => '2',
                'lastlogin' => '17-10-2024 11:36:16',
                'siteaccess' => '1-1-1-1',
                'navn' => 'Shankar general user',
                'oensker_email_ved_specifik_sag' => '1',
                'email' => '',
                'usynlig' => '0',
                'hideuser' => '1',
            ],
        ];

        // Loop through each company and insert users if they don't exist
        foreach ($companies as $company) {
            // Skip the company if we've already processed it (based on company_id)
            if (in_array($company->id, $this->processedCompanies)) {
                $this->info("Company {$company->id} has already been processed. Skipping...");
                continue; // Skip this company and move to the next one
            }

            // Mark the company as processed by adding its ID to the array
            $this->processedCompanies[] = $company->id;

            // Set dynamic database connection for each company
            config([
                "database.connections.{$company->db_name}" => [
                    'driver' => 'mysql',
                    'host' => env('DB_ROOT_HOST', '127.0.0.1'),
                    'port' => env('DB_ROOT_PORT', '3306'),
                    'database' => $company->db_name,
                    'username' => $company->dbuser_name,
                    'password' => $company->db_password,
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ]
            ]);

            // Insert users into the company database if they don't exist
            foreach ($users as $user) {
                // Check if the user already exists in the company's database
                $userExists = DB::connection($company->db_name)
                    ->table('cloud_sso_users')
                    ->where('brugernavn', $user['brugernavn'])
                    ->exists();

                if (!$userExists) {
                    // If user doesn't exist, insert the user
                    DB::connection($company->db_name)
                        ->table('cloud_sso_users')
                        ->insert($user);
                    $this->info("User {$user['brugernavn']} created for company: {$company->db_name}.");
                }
                //else {
                //     // If user already exists, skip the insert
                //     $this->info("User {$user['brugernavn']} already exists in company: {$company->db_name}. Skipping.");
                // }
            }
        }
    }
}
