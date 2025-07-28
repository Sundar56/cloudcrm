<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Traits\sendNotification;

class CreateCompanyDatabaseJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, sendNotification;
    protected $companyId;
    protected $companyName;

    /**
     * Create a new job instance.
     */
    public function __construct(int $companyId, string $companyName)
    {
        $this->companyId = $companyId;
        $this->companyName = $companyName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // set_time_limit(0);
        ini_set('max_execution_time', '1200');
        ini_set('memory_limit', '2108M');
        $companyId   = $this->companyId;
        $companyName = $this->companyName;
        $dbName      = 'comp_' . strtolower($companyName);
        $dbHost      = env("DB_ROOT_HOST");
        $serverIp    = env("SERVER_IP");
        $dbUsername  = $dbHost == 'localhost' ? 'root' : env("DB_ROOT_USERNAME");
        $dbPassword  = $dbHost == 'localhost' ? null : env("DB_ROOT_PASSWORD");

        $channel = 'migrationchannel';
        $this->sendNotification($channel, [
            'companyId' => $companyId,
            'state'     => 0,
            'message'   => "Processnig For Company database",
        ]);

        error_log('companyId::' . $companyId);
        error_log('dbUsername' . $dbUsername);
        error_log('dbHost' . $dbHost);
        try {
            CompanyDatabase::create([
                'company_id'  => $companyId,
                'db_name'     => $dbName,
                'dbuser_name' => $dbUsername,
                'db_password' => $dbPassword,
                'collation'   => '',
            ]);
            $this->sendNotification($channel, [
                'companyId' => $companyId,
                'state'     => 1,
                'message'   => "Company database has been created",
            ]);
        } catch (\Exception $e) {
            error_log('Company Database not created::' . $e->getMessage());
        }

        error_log('company database created');
        $host = $dbHost == $serverIp ? 'localhost' : '%';
        $createUserQuery = "CREATE USER IF NOT EXISTS '$dbUsername'@'$host' IDENTIFIED BY '$dbPassword'";
        error_log('createuserQuery::' . $createUserQuery);
        error_log('dbname::' . $dbName);

        $createDatabase = "CREATE DATABASE IF NOT EXISTS `$dbName`";
        error_log('createDatabase::' . $createDatabase);
        DB::connection('mysql_root')->statement($createDatabase);
        $privileges = "GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUsername'@'$host' WITH GRANT OPTION";

        // Set the dynamic database connection
        $this->setDynamicDatabaseConnection($dbName, $dbUsername, $dbPassword);

        Company::where('id', $companyId)->update(['migrate_status' => 1]);
        //Test Database connection
        try {
            DB::connection($dbName)->getPdo();
            error_log('Successfully connected to the databas::' . $dbName);
        } catch (\Exception $e) {
            error_log('MySQL connection failed::' . $e->getMessage());
            return; // Stop execution
        }
        error_log('Migration started at: ' . now());
        // Run migrations with progress bar
        $this->runMigrations($dbName, $companyId, $channel);
        error_log('Migration ended at: ' . now());

        // $this->sendNotification($channel, 2);

        error_log('Start console for cloud_variabler' . now());
        Artisan::call('app:populate-general-settings', [
            'companydbname' => $dbName
        ]);
        error_log('End console for cloud_variabler' . now());
        // $this->sendNotification($channel, 3);
        // error_log('Start console command for cloud_sso_users' . now());
        // Artisan::call('insert:users', [
        //     'company_id' => $companyId
        // ]);
        // error_log('End console command for cloud_sso_users' . now());
        // error_log('Users inserted for company ' . $companyName);      
        error_log('Start console command for roles creation' . now());
        Artisan::call('app:populate-company-roles', [
            'companyId' => $companyId
        ]);
        error_log('End console command for roles creation' . now());
        // $this->sendNotification($channel, 4);


        error_log('Roles inserted for company ' . $companyName);

        error_log('Start seeding CompanyRolesPermission' . now());
        Artisan::call('db:seed', [
            '--class' => 'CompanyRolesPermission'
        ]);
        error_log('End seeding CompanyRolesPermission' . now());
        // $this->sendNotification($channel, 5);

        error_log('Start console for page module list' . now());
        Artisan::call('app:customer-page-modules', [
            'dbName' => $dbName
        ]);
        error_log('End console for page module list' . now());

        error_log('Start console for updated setting modules and option' . now());
        Artisan::call('update:setting-options', [
            'dbName' => $dbName
        ]);
        error_log('End console for updated setting modules and option' . now());


        error_log('Start console command for App modules' . now());
        Artisan::call('app:run-company-app-modules-command', [
            'companyId' => $companyId
        ]);
        error_log('End console command for App modules' . now());

        error_log('Files & Folders Transfer started at: ' . now());
        $this->copyCompanyFiles($companyName, $companyId, $channel);
        error_log('Files & Folders Transfer ended at: ' . now());

        $updated = Company::where('id', $companyId)
        ->update([
            'migrate_status' => '7',
            'lastfile_updated_at' => now()
        ]);

        $this->sendNotification($channel, [
            'companyId' => $companyId,
            'state'     => 7,
            'message'   => "All steps have been successfully completed",
        ]);
    }
    public function runMigrations(string $dbName, $companyId, $channel): void
    {
        try {
            // set_time_limit(0);
            // Get the migration files from the path
            $migrationsPath = database_path('migrations/companymigrations');
            $migrationFiles = File::files($migrationsPath);

            // Total number of migrations
            $totalMigrations = count($migrationFiles);
            $completedMigrations = 0;
            error_log("Starting migrations... Total migrations: $totalMigrations");

            // Initialize the console output and progress bar
            $output = new ConsoleOutput();
            $progressBar = new ProgressBar($output, $totalMigrations);

            // Start the progress bar
            $progressBar->start();

            // Iterate over each migration file and apply it
            foreach ($migrationFiles as $migration) {
                $migrationName = pathinfo($migration)['basename'];
                // Run each migration manually
                $exitCode = Artisan::call('migrate', [
                    '--database' => $dbName,
                    '--path' => "database/migrations/companymigrations/{$migrationName}",
                ]);
                // Log progress after each migration
                $progressBar->advance();
                // You can also log any errors or exit code for each migration
                if ($exitCode !== 0) {
                    error_log("Migrations failed:. $migrationName");
                }

                // Update the count of completed migrations
                $completedMigrations++;

                // Update `migrate_status` based on progress
                if ($completedMigrations == 38) {
                    Company::where('id', $companyId)->update(['migrate_status' => 2]);
                    $this->sendNotification($channel, [
                        'companyId' => $companyId,
                        'state'     => 2,
                        'message'   => "CRM Tables migration has been created",
                    ]);
                }
                if ($completedMigrations == 41) {
                    Company::where('id', $companyId)->update(['migrate_status' => 3]);
                    $this->sendNotification($channel, [
                        'companyId' => $companyId,
                        'state'     => 3,
                        'message'   => "SSO Tables migration has been created",
                    ]);
                }
            }
            // Finish the progress bar
            $progressBar->finish();
            // Final update after all migrations
            if ($completedMigrations == $totalMigrations) {
                Company::where('id', $companyId)->update(['migrate_status' => 4]);
                $this->sendNotification($channel, [
                    'companyId' => $companyId,
                    'state'     => 4,
                    'message'   => "Shop Tables migration has been created",
                ]);
            }
            error_log("Migrations completed!");
        } catch (\Exception $e) {
            error_log('Failed to runMigrations::' . $e->getMessage());
        }
    }
    public function setDynamicDatabaseConnection($dbName, $dbUsername, $dbPassword)
    {
        config([
            "database.connections.$dbName" => [
                'driver' => 'mysql',
                'host' => env('DB_ROOT_HOST', '127.0.0.1'),
                'port' => env('DB_ROOT_PORT', '3306'),
                'database' => $dbName,
                'username' => $dbUsername,
                'password' => $dbPassword,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ]);
    }
    public function copyCompanyFiles($companyName, $companyId, $channel)
    {
        $companyFolderPath = env("COMPANY_FOLDER_CREATION");
        error_log('Folderpath::' . $companyFolderPath);
        $folderPath = $companyFolderPath . DIRECTORY_SEPARATOR . $companyName;
        $sourcePath = env("COMPANY_FOLDER_SOURCE_PATH");
        if (!file_exists($folderPath)) {
            if (mkdir($folderPath, 0755, true)) {
                error_log('Created directory::' . $folderPath);
            } else {
                error_log('Failed to create directory::' . $folderPath);
                return; // Exit if directory creation fails
            }
        } else {
            error_log('Directory already exists::' . $folderPath);
        }
        // Define the paths for cloudcrm and cloud-router-profile directories
        $cloudCrmFolderPath = $folderPath . DIRECTORY_SEPARATOR . 'cloudcrm' . DIRECTORY_SEPARATOR . 'Config';
        $cloudRouterProfileFolderPath = $folderPath . DIRECTORY_SEPARATOR . 'cloud-router-profile' . DIRECTORY_SEPARATOR . 'Config';
        $cloudSSOPath = $folderPath . DIRECTORY_SEPARATOR . 'cloudsso' . DIRECTORY_SEPARATOR . 'Config';

        $customer_portal = $folderPath . DIRECTORY_SEPARATOR . 'customer-portal' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploadassets' . DIRECTORY_SEPARATOR . 'company' . DIRECTORY_SEPARATOR . $companyId;

        if (!file_exists($customer_portal)) {
            if (mkdir($customer_portal, 0755, true)) {
                error_log('Created directory::' . $customer_portal);
            } else {
                error_log('Failed to create directory::' . $customer_portal);
                return; // Exit if directory creation fails
            }
        } else {
            error_log('Directory already exists::' . $customer_portal);
        }
        // $source_path = 'F:\\crm\\cloudcrm\\laravelcrm\\public\\uploadassets\\companies\\' . $companyId;
        $basePath = env('UPLOAD_ASSETS_PATH');
        $source_path = $basePath . DIRECTORY_SEPARATOR . $companyId;
        // $source_path = 'D:\\sundar\\crm_admin_portal_api\\crm_admin_api _service\\public\\uploadassets\\company\\' . $companyId;
        try {
            $this->copyImagesToCustomerPortal($source_path, $customer_portal);

            // Ensure the cloudcrm\Config and cloud-router-profile\Config directories exist and create apiconfig.php
            $this->createDirectoryIfNotExists($cloudCrmFolderPath);
            $this->createDirectoryIfNotExists($cloudRouterProfileFolderPath);
            $this->createDirectoryIfNotExists($cloudSSOPath);

            // Create apiconfig.php file inside each Config folder
            $this->createApiConfigFile($cloudCrmFolderPath . DIRECTORY_SEPARATOR . 'apiconfig.php');
            $this->createApiConfigFile($cloudRouterProfileFolderPath . DIRECTORY_SEPARATOR . 'apiconfig.php');
            $this->createApiConfigFile($cloudSSOPath . DIRECTORY_SEPARATOR . 'apiconfig.php');
        } catch (\Exception $e) {
            error_log('Failed to copy files and folders::' . $e->getMessage());
        }
        Company::where('id', $companyId)->update(['migrate_status' => 5]);
        $this->sendNotification($channel, [
            'companyId' => $companyId,
            'state'     => 5,
            'message'   => "Config File Created",
        ]);
        // Copy files and folders from the source path to the new folder
        try {
            $this->copyDirectory($sourcePath, $folderPath);
            Company::where('id', $companyId)->update(['migrate_status' => 6]);

            error_log('Files and folders copied from::' . $sourcePath . " to " . $folderPath);
            $this->sendNotification($channel, [
                'companyId' => $companyId,
                'state'     => 6,
                'message'   => "Folders created & Data dump has been completed",
            ]);
        } catch (\Exception $e) {
            error_log('Failed to copy files and folders::' . $e->getMessage());
        }
    }
    private function copyDirectory($source, $destination)
    {
        try {
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $items = scandir($source);
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') continue;
                $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
                $destinationPath = $destination . DIRECTORY_SEPARATOR . $item;

                if (is_dir($sourcePath)) {
                    // If the item is a directory, call this function recursively
                    $this->copyDirectory($sourcePath, $destinationPath);
                } else {
                    // If the item is a file, copy it
                    copy($sourcePath, $destinationPath);
                }
            }
        } catch (\Exception $e) {
            error_log('Failed to copyDirectory::' . $e->getMessage());
        }
    }
    /**
     * Customer portal
     */
    private function copyImagesToCustomerPortal($sourcePath, $destinationPath)
    {
        try {
            // Ensure the destination directory exists
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // Open the source directory
            $dir = opendir($sourcePath);
            if ($dir) {
                // Loop through the files in the source directory
                while (($file = readdir($dir)) !== false) {
                    if ($file != '.' && $file != '..') {
                        // Copy each file to the destination directory
                        copy($sourcePath . DIRECTORY_SEPARATOR . $file, $destinationPath . DIRECTORY_SEPARATOR . $file);
                    }
                }
                closedir($dir);
            }
        } catch (\Exception $e) {
            error_log('Failed to copyImagesToCustomerPortal::' . $e->getMessage());
        }
    }

    // create directory if it doesn't exist
    private function createDirectoryIfNotExists($path)
    {
        try {
            if (!file_exists($path)) {
                if (mkdir($path, 0755, true)) {
                    error_log('Created directoryt::' . $path);
                } else {
                    error_log('Failed to create directory::' . $path);
                }
            } else {
                error_log('Directory already exists::' . $path);
            }
        } catch (\Exception $e) {
            error_log('Failed to createDirectoryIfNotExists::' . $e->getMessage());
        }
    }
    // create the apiconfig.php file
    private function createApiConfigFile($filePath)
    {
        try {
            $companyId = $this->companyId;
            $company   = Company::where('id', $companyId)->first();
            $dbDetails = CompanyDatabase::where('company_id', $companyId)->first();
            $hostName   = env("DB_ROOT_HOST");

            if (!$company) return error_log('Company not found::' . $companyId);

            if (!file_exists($filePath)) {

                // $siteType        = (strpos($filePath, 'cloudcrm') !== false) ? 'crm' : 'cms';
                if (strpos($filePath, 'cloudcrm') !== false) {
                    $siteType = 'crm';
                } elseif (strpos($filePath, 'cloudsso') !== false) {
                    $siteType = 'sso';
                } elseif (strpos($filePath, 'cloud-router-profile') !== false) {
                    $siteType = 'cms';
                } else {
                 $siteType = 'default';
                }
                
             $moduleAccessUrl = 'https://crmapi.sitecare.org/api/CheckCompanyModuleAccess';
             $siteAccessUrl   = 'https://crmapi.sitecare.org/api/checkapplicationaccess';
             $fileContent     = "<?php\n 
             define('APIKEY', '{$company->apikey}');\n 
             define('APIKSECRET', '{$company->apisecret}');\n 
             define('SITETYPE', '{$siteType}');\n 
             define('MODULEACCESSURL', '$moduleAccessUrl');\n 
             define('SITEACCESSURL', '$siteAccessUrl');\n
             define('DBNAME','$dbDetails->db_name');\n
             define('USERNAME','$dbDetails->dbuser_name');\n
             define('PASSWORD','$dbDetails->db_password');\n
             define('HOSTNAME','$hostName');";
                // Try to create the apiconfig.php file
             try {
                file_put_contents($filePath, $fileContent);
                error_log('Created apiconfig.php file at::' . $filePath);
            } catch (\Exception $e) {
                error_log('Failed to create apiconfig.php file::' . $e->getMessage());
            }
        } else {
            error_log('apiconfig.php already exists at::' . $filePath);
        }
    } catch (\Exception $e) {
        error_log('Failed to createApiConfigFile::' . $e->getMessage());
    }
}
}
