<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CustomerPageModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:customer-page-modules {dbName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Customer Page Modules for a specific database';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName     = $this->argument('dbName');
        $dbHost     = env("DB_ROOT_HOST", '127.0.0.1');
        $dbUsername = $dbHost == 'localhost' ? 'root' : env("DB_ROOT_USERNAME");
        $dbPassword = $dbHost == 'localhost' ? null : env("DB_ROOT_PASSWORD");

            config([
            "database.connections.$dbName" => [
                'driver'    => 'mysql',
                'host'      => $dbHost,
                'port'      => env('DB_ROOT_PORT', '3306'),
                'database'  => $dbName,
                'username'  => $dbUsername,
                'password'  => $dbPassword,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]
        ]);

        try {
            DB::connection($dbName)->getPdo();
            error_log('Successfully connected to the database: ' . $dbName);
        } catch (\Exception $e) {
            error_log('MySQL connection failed: ' . $e->getMessage());
            return; // Stop execution if connection fails
        }
          // Prepare data for seeding the PageModules table
        $pageModules = [
            'Dashboard'    => 'dashboard',
            'Company'      => 'company',
            'Employees'    => 'employees',
            'Integrations' => 'integrations',
            'Settings'     => 'settings',
        ];

        $currentTimestamp = now();
        foreach ($pageModules as $pagename => $pageslug) {
            try {
                DB::connection($dbName)->table('tbl_pagemodules')->updateOrInsert(
                    ['pageslug' => $pageslug],
                    [
                        'pagename'       => $pagename,
                        'pageslug'       => $pageslug,
                        'activitystatus' => 0,
                        'created_at'     => $currentTimestamp,
                        'updated_at'     => $currentTimestamp,
                    ]
                );
                error_log("PageModule '$pagename' inserted/updated successfully.");
            } catch (\Exception $e) {
                error_log("Failed to insert/update PageModule '$pagename': " . $e->getMessage());
            }
        }

        $this->info('PageModules seeding completed.');
    }
   
}
