<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Api\Systemadmin\Modules\Roles\Models\RoleHasPermission;
use Illuminate\Support\Facades\Artisan;

class SuperAdminPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'superadmin')->first();
        RoleHasPermission::where('role_id', $role->id)->delete();
        Artisan::call('permission:create-permission-routes');
        $block = ".block";
        // $customer = "customer";
        $permissions = Permission::where('name', 'not like', '%' . $block . '%')->pluck('id', 'id')->all();
        // ->where('name', 'not like', '%' . $customer . '%')
        
        $data = [];
        foreach ($permissions as $permission) {
            $data[] = [
                'permission_id' => $permission,
                'role_id'       => $role->id,
            ];
        }
        $role_has_permissions = RoleHasPermission::insert($data);
        $this->command->info('Superadmin Permissions Updated successfully.');
    }
}
//php artisan db:seed --class=SuperadminPermission