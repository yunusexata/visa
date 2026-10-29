<?php

namespace Database\Seeders\User;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => "Admin",
            'username' => "Admin",
            'email' => "admin@gmail.com",
            'password' => Hash::make("visa1234"),
        ]);

        $user->assignRole(User::ROLE_SUPERADMIN);
    }
}
