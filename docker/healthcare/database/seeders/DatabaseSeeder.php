<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin mặc định — đổi mật khẩu sau khi deploy production
        User::firstOrCreate(
            ['email' => 'admin@hospital.local'],
            [
                "uuid" => '00000000-0000-0000-0000-000000000001',
                'username' => 'admin',
                'password' => Hash::make('Admin@123456'),
                'status'   => 'active',
            ]
        );
    }
}
