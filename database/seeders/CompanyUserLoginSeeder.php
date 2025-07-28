<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;

class CompanyUserLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $companies = CompanyDatabase::all();
        $companies = CompanyDatabase::where('id', 1)->get();
        foreach ($companies as $company) {
            // Set dynamic database connection
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

            // Check if the users already exist
            foreach ($users as $user) {
                $userExists = DB::connection($company->db_name)
                    ->table('cloud_sso_users')
                    ->where('brugernavn', $user['brugernavn'])
                    ->exists();

                if (!$userExists) {
                    // If user doesn't exist, insert the user
                    DB::connection($company->db_name)
                        ->table('cloud_sso_users')
                        ->insert($user);

                    // Log info about the inserted user
                    $this->command->info("User {$user['brugernavn']} created for company: {$company->db_name}.");
                } else {
                    // If user already exists, log that the user was skipped
                    $this->command->info("User {$user['brugernavn']} already exists in company: {$company->db_name}. Skipping.");
                }
            }
        }
    }
}
// php artisan db:seed --class=CompanyUserLoginSeeder