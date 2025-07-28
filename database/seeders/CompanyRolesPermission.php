<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Api\Systemadmin\Modules\Roles\Models\RoleHasPermission;
use App\Models\Module;
use Illuminate\Support\Facades\Artisan;

class CompanyRolesPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleTypes = [2, 3, 4, 5];
        $roles = Role::where('name', 'admin')->where('type', 1)->pluck('id');
        RoleHasPermission::whereIn('role_id', $roles)->delete();

        Artisan::call('permission:create-permission-routes');

        $data = [];
        foreach ($moduleTypes as $moduleType) {
            $moduleIds = Module::where('module_type', $moduleType)->pluck('id');
            $block = ".block";

            foreach ($moduleIds as $moduleId) {
                // $permissions = Permission::where('module_id', $moduleId)->pluck('id');
                $permissions = Permission::where('name', 'not like', '%' . $block . '%')->where('module_id', $moduleId)->pluck('id');

                foreach ($roles as $roleId) {
                    foreach ($permissions as $permissionId) {
                        $data[] = [
                            'permission_id' => $permissionId,
                            'role_id'       => $roleId,
                            'module_type'   => $moduleType,
                        ];
                    }
                }
            }
        }

        RoleHasPermission::insert($data);
        $this->command->info('Company Roles Permissions Updated successfully.');
    }
}
// php artisan db:seed --class=CompanyRolesPermission