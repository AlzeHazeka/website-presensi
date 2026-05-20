<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleSync;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('TEST_ADMIN_EMAIL', 'admin@test.com');
        $username = env('TEST_ADMIN_USERNAME', 'admin.test');
        $password = env('TEST_ADMIN_PASSWORD', 'password');
        $nama = env('TEST_ADMIN_NAME', 'Admin Test');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => $nama,
                'username' => $username,
                'password' => Hash::make($password),
                'status' => 'aktif',
                'role' => Roles::ADMIN,
            ]
        );

        RoleSync::sync($user, Roles::ADMIN);
    }
}

