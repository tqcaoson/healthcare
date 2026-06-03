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
        $admin = User::firstOrCreate(
            ['email' => 'tqcaoson@gmail.com'],
            [
                'uuid'     => '00000000-0000-0000-0000-000000000001',
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        // Ensure role is set even if user existed before this fix
        if (empty($admin->role)) {
            $admin->update(['role' => 'admin']);
        }

        $this->call(DemoSeeder::class);
    }
}
