<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;

class UpdateAllSettingsandPageModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-settings-page-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update setting options and page modules for all company databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companies = CompanyDatabase::all(); 
        foreach ($companies as $company) {
            $dbName = $company->db_name;
            $this->info("Updating settings for database: $dbName");

            Artisan::call('update:setting-options', [
                'dbName' => $dbName,
            ]);
            $this->info("Completed update for database: $dbName - Setting options");

            Artisan::call('app:customer-page-modules', [
                'dbName' => $dbName,
            ]);
            $this->info("Completed update for database: $dbName - Customer page modules");
        }

    $this->info('All company database settings and customer page and AccessControl modules updated successfully!');
    }
}
