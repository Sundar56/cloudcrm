<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class CreateShopModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $module_type = 4;
        $modules = [
            [
                'name'       => 'Forhandler',
                'order'      => '82',
                'slug'       => 'forhandler',
                'main_module' => 'forhandler',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Indkøbskurv',
                'order'      => '83',
                'slug'       => 'kurv',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Laes vores betingelser',
                'order'      => '84',
                'slug'       => 'betingelser',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // [
            //     'name'       => 'Forside',
            //     'order'      => '82',
            //     'slug'       => 'forside',
            //     'main_module' => '',
            //     'sub_module' => '',
            //     'status'     =>  '1',
            //     'module_type'     =>  $module_type,
            //     'created_at' => date('Y-m-d H:i:s'),
            //     'updated_at' => date('Y-m-d H:i:s')
            // ],
            [
                'name'       => 'produkter',
                'order'      => '85',
                'slug'       => 'produkter',
                'main_module' => 'produkter',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'wifi-netvaerk',
                'order'      => '86',
                'slug'       => 'loesninger_wifi-netvaerk',
                'main_module' => 'loesninger',
                'sub_module' => 'wifi-netvaerk',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'radio-link',
                'order'      => '87',
                'slug'       => 'loesninger_radio-link',
                'main_module' => 'loesninger',
                'sub_module' => 'radio-link',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'infrastruktur',
                'order'      => '88',
                'slug'       => 'loesninger_infrastruktur',
                'main_module' => 'loesninger',
                'sub_module' => 'infrastruktur',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'm2m-netvaerk',
                'order'      => '89',
                'slug'       => 'loesninger_m2m-netvaerk',
                'main_module' => 'loesninger',
                'sub_module' => 'm2m-netvaerk',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'aktiviteter',
                'order'      => '90',
                'slug'       => 'aktiviteter',
                'main_module' => 'aktiviteter',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Service',
                'order'      => '91',
                'slug'       => 'teknisk-support',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Profile',
                'order'      => '92',
                'slug'       => 'profil',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Referencer',
                'order'      => '93',
                'slug'       => 'referencer',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Kontakt',
                'order'      => '94',
                'slug'       => 'kontakt',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Kommende events',
                'order'      => '95',
                'slug'       => 'events',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // [
            //     'name'       => 'Laes mere her',
            //     'order'      => '94',
            //     'slug'       => '',
            //     'main_module' => '',
            //     'sub_module' => '',
            //     'status'     =>  '1',
            //     'module_type'     =>  $module_type,
            //     'created_at' => date('Y-m-d H:i:s'),
            //     'updated_at' => date('Y-m-d H:i:s')
            // ],
            [
                'name'       => 'Nyheder og presse',
                'order'      => '96',
                'slug'       => 'news',
                'main_module' => '',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            // [
            //     'name'       => 'Laes videre',
            //     'order'      => '96',
            //     'slug'       => '',
            //     'main_module' => '',
            //     'sub_module' => '',
            //     'status'     =>  '1',
            //     'module_type'     =>  $module_type,
            //     'created_at' => date('Y-m-d H:i:s'),
            //     'updated_at' => date('Y-m-d H:i:s')
            // ], 
            [
                'name'       => 'Loesninger',
                'order'      => '97',
                'slug'       => 'loesninger',
                'main_module' => 'loesninger',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'       => 'Min konto',
                'order'      => '98',
                'slug'       => 'min-konto',
                'main_module' => 'min-konto',
                'sub_module' => '',
                'status'     =>  '1',
                'module_type'     =>  $module_type,
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

        $this->command->info('Shop Modules created successfully.');
    }
}
// php artisan db:seed --class=CreateShopModulesSeeder 