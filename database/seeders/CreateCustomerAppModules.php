<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class CreateCustomerAppModules extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $modules = [
            [
                'name'        => 'Sales',
                'order'       => '99',
                'slug'        => 'app_sales',
                'status'      => '1',
                'module_type' => '5',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ],
            [
               'name'        => 'CRM',
               'order'       => '100',
               'slug'        => 'app_crm',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
           ],
           [
               'name'        => 'White Board',
               'order'       => '101',
               'slug'        => 'app_whiteboard',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
           ],
           [
               'name'        => 'Password Manager',
               'order'       => '102',
               'slug'        => 'app_passwordmanager',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
           ],
           [
               'name'        => 'Webshop',
               'order'       => '103',
               'slug'        => 'app_webshop',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
           ],
           [
               'name'        => 'Stock',
               'order'       => '104',
               'slug'        => 'app_stock',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],  
            [
               'name'        => 'CMS',
               'order'       => '105',
               'slug'        => 'app_cms',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],        
            [
               'name'        => 'Admin App',
               'order'       => '106',
               'slug'        => 'app_admin',
               'status'      => '1',
               'module_type' => '5',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],   
            [
               'name'        => 'My Company',
               'order'       => '107',
               'slug'        => 'mycompany',
               'main_module' => 'Admin App',
               'status'      => '1',
               'module_type' => '6',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],  
            [
               'name'        => 'Employees',
               'order'       => '108',
               'slug'        => 'employees',
               'main_module' => 'Admin App',
               'status'      => '1',
               'module_type' => '6',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],  
            [
               'name'        => 'Integrations',
               'order'       => '109',
               'slug'        => 'integrations',
               'main_module' => 'Admin App',
               'status'      => '1',
               'module_type' => '6',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],   
            [
               'name'        => 'Settings',
               'order'       => '110',
               'slug'        => 'settings',
               'main_module' => 'Admin App',
               'status'      => '1',
               'module_type' => '6',
               'created_at'  => date('Y-m-d H:i:s'),
               'updated_at'  => date('Y-m-d H:i:s')
            ],      
        ];
        foreach ($modules as $module) {
            Module::updateOrInsert(
                ['slug' => $module['slug']],
                $module 
            );
        }
        $this->command->info('Customer App Modules created successfully.');
    }
}
// php artisan db:seed --class=CreateCustomerAppModules 