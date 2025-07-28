<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateCrmRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company_id = 1;
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
        $this->command->info('Roles created successfully.');
    }
}
// php artisan db:seed --class=CreateCrmRolesSeeder