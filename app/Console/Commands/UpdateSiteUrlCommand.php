<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\Company;

class UpdateSiteUrlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-site-url-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the site url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Would you like to update the site URL', true)) {

            $company_name = $this->ask('Enter the company name');
            $Company_details = Company::with('company_database')->where('company_name',$company_name)->first();
            if (!$Company_details) {
                $this->error('Company not found.');
                  1;
            }
            if ($Company_details) {
                // Set dynamic database connection for each company
            $company = $Company_details->company_database;
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
            $site_domine = $this->ask('Enter the SiteDomain');
           $this->updateCloudVariabler($company->db_name,$site_domine,1);

           $Successfull_login_url = $this->ask('Enter the SuccessFullLogin URL');
           $this->updateCloudVariabler($company->db_name,$Successfull_login_url,2);

           $ss_path = $this->ask('Enter the SSOPath');
           $this->updateCloudVariabler($company->db_name,$ss_path,3);

           $cms_url = $this->ask('Enter the CMS URL');
           $this->updateCloudVariabler($company->db_name,$cms_url,44);

           $crm_url = $this->ask('Enter the CRM URL');
           $this->updateCloudVariabler($company->db_name,$crm_url,47);
                        // $this->line("Name: {$cloud_variabler}");
                    // $this->info("User {$user['brugernavn']} created for company: {$company->db_name}.");
            // $this->info('User updated successfully.');
            // $domin = $this->ask('Enter the Site Domain');
            // $this->line("Name: {$domin}");
            $this->info('Site URL updated successfully.');
            }
            
        } else {
            $this->info('Update canceled.');
        }
    }

    public function updateCloudVariabler($db_name,$value,$id){
        $cloud_variabler = DB::connection($db_name)
        ->table('cloud_variabler')
        ->where('id', $id)
        ->update( ['vaerdi' => $value] );

        $this->info('Updated successfully.');
    }
}
