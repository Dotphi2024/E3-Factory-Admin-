<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'masteradmin@gmail.com')->first();
        if(!$user){
            $user = new User();
            $user->name = 'Master Admin';
            $user->email = 'masteradmin@gmail.com';
            $user->password = Hash::make('8520');
            $user->user_type='master-admin';
            $user->save();
        }
    }
}