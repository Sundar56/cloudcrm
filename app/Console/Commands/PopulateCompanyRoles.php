<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class PopulateCompanyRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-company-roles {companyId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company_id = $this->argument('companyId');
        $roles = Role::insert([
            [
                'name'       => 'admin',
                'display_name'      => 'Admin',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'sales',
                'display_name'      => 'Sales',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'project',
                'display_name'      => 'Project',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'logistic',
                'display_name'      => 'Logistic',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'support',
                'display_name'      => 'Support',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'generaluser',
                'display_name'      => 'General user',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'supervisor',
                'display_name'      => 'Supervisor',
                'company_id'       => $company_id,
                'type'     =>  1,
                'guard_name'     =>  'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ]);
         $this->info('Roles created successfully.');
    }
}
