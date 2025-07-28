<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class CreateCrmModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name'       => 'Dashboard',
                'order'      => '12',
                'slug'       => 'dashboard',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Kunder',
                'order'      => '13',
                'slug'       => 'kunder',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Leads',
                'order'      => '14',
                'slug'       => 'leads',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Opgaver',
                'order'      => '15',
                'slug'       => 'task',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Salgssager',
                'order'      => '16',
                'slug'       => 'salgssager',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Servicesager',
                'order'      => '17',
                'slug'       => 'servicesager',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Rma',
                'order'      => '18',
                'slug'       => 'rma',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Loesninger',
                'order'      => '19',
                'slug'       => 'loesninger',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Serviceaftaler',
                'order'      => '20',
                'slug'       => 'serviceaftaler',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Abonnementer',
                'order'      => '21',
                'slug'       => 'abonnementer',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Lager',
                'order'      => '22',
                'slug'       => 'lager',
                'status'     =>  '1',
                'module_type'     =>  '2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],

        ];
        foreach ($modules as $module) {
            Module::updateOrInsert(
                ['slug' => $module['slug']],
                $module
            );
        }
        $this->command->info('Modules created successfully.');
    }
}

// php artisan db:seed --class=CreateCrmModulesSeeder 