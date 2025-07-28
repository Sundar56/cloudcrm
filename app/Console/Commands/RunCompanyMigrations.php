<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RunCompanyMigrations extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-company-migrations {companyId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
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

        if ($companyId) {
            // Run migrations for the specified company
            $this->info("Processing migrations for company ID: $companyId");
            $this->processCompanyMigrations($companyId);
        } else {
            // Run migrations for all companies
            $this->info('Processing migrations for all companies...');
            $this->processAllCompaniesMigrations();
        }

        return 0; // Success status code

    }

    /**
     * Process migrations for a specific company.
     *
     * @param int $companyId
     */
    protected function processCompanyMigrations($companyId)
    {

        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($companyId);
        if (!$dbDetails) {
            $this->error("Database details not found for company ID: $companyId");
            return;
        }
        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            $this->error('Failed to connect to database: ' . $dbDetails->db_name);
            return;
        }

        $this->info('Running migrations for database: ' . $dbDetails->db_name);
        Artisan::call('migrate', [
            '--database' => $dbDetails->db_name,
            '--path'     => 'database/migrations/companymigrations',
        ]);
        $this->info(Artisan::output());
    }

    /**
     * Process migrations for all companies.
     */
    protected function processAllCompaniesMigrations()
    {
        $companies = DB::table('company_databases')->get();

        foreach ($companies as $company) {
            $this->info('Processing company ID: ' . $company->company_id);
            $companyId = $company->company_id;

            $dbDetails = $this->companyDatabaseService->getDatabaseDetails($companyId);
            if (!$dbDetails) {
                $this->error("Database details not found for company ID: $companyId");
                return;
            }
            $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

            if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
                $this->error('Failed to connect to database: ' . $dbDetails->db_name);
                return;
            }

            $this->info('Running migrations for database: ' . $dbDetails->db_name);
            Artisan::call('migrate', [
                '--database' => $dbDetails->db_name,
                '--path'     => 'database/migrations/companymigrations',
            ]);
            $this->info(Artisan::output());
        }

        $this->info('All migrations completed.');
    }
}
