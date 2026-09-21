<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = config('permissions-modules.modules');

        foreach($modules as $row){
            if(!Permission::where('name',$row." add")->exists()){
                Permission::create(['name' => $row . " add",]);
                Permission::create(['name' => $row . " edit"]);
                Permission::create(['name' => $row . " delete"]);
                Permission::create(['name' => $row . " list"]);
                Permission::create(['name' => $row . " view"]);
            }
        }

        $users = User::where('user_type','master-admin')->get();
        $all_permissions=Permission::all();
        $role = Role::where('name','Admin')->first();
        if(!$role){
            $role = Role::create([
                'name'=>'Admin',
                'guard_name'=>'web'
            ]);
        }

        $role->givePermissionTo($all_permissions);
        foreach($users as $user){
            $user->assignRole($role->name);
        }
    }
}
