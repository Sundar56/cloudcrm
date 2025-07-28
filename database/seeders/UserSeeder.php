<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Api\Systemadmin\Modules\Adminuser\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user         = User::where('email', 'superadmin@gmail.com')->first();
        $password     = 'crmlaravel11';
        $hashPassword = Hash::make($password);
        if (empty($user)) {
            $user = User::create([
                'name'             => 'Superadmin',
                'email'            => 'superadmin@gmail.com',
                'password'         =>  $hashPassword,
                'user_phone'       =>  9876541230,
                'user_displayname' => 'SuperAdmin',
            ]);
            $this->command->info('Superadmin created successfully.');
            $superAdminRole = Role::create(['name' => 'superadmin', 'display_name' => 'SuperAdmin']);
            // $supportAdminRole = Role::create(['name' => 'supportadmin', 'display_name' => 'SupportAdmin', 'guard_name' => 'web']);
            // $user->assignRole([$superAdminRole->name]);
            $role = Role::findByName('superadmin');
            $user->assignRole($role);
        }
    }
}
