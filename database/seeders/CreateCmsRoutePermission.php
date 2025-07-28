<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use Spatie\Permission\Models\Permission;

class CreateCmsRoutePermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = Module::where('module_type', 3)->pluck('slug','id');
        $actions = ['index', 'create', 'view', 'store', 'edit', 'update', 'delete'];
        if($modules->isNotEmpty()){
            $permissions = []; // Array to collect new permissions

    // Generate all possible routes
    $allRoutes = [];
    foreach ($modules as $id => $module) {
        foreach ($actions as $action) {
            $allRoutes[] = "cms.$module.$action";
        }
    }

    // Fetch existing permissions for these routes
    $existingPermissions = Permission::whereIn('name', $allRoutes)
        ->pluck('name')
        ->toArray();

    // Prepare new permissions for batch insertion
    foreach ($modules as $id => $module) {
        foreach ($actions as $action) {
            $route = "cms.$module.$action";
            if (!in_array($route, $existingPermissions)) {
                $permissions[] = [
                    'name' => $route,
                    'module_id' => $id, // Use the ID from the modules
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
    }

    // Batch Insert Permissions
    if (!empty($permissions)) {
        Permission::insert($permissions);
    }
            $this->command->info('Permission routes added successfully.');
        }else {
            $this->command->warn('No modules found for the specified type.');
        }
    }
}
