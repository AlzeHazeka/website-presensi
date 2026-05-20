<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleSync;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('TEST_KARYAWAN_EMAIL', 'karyawan@test.com');
        $username = env('TEST_KARYAWAN_USERNAME', 'karyawan.test');
        $password = env('TEST_KARYAWAN_PASSWORD', 'password');
        $nama = env('TEST_KARYAWAN_NAME', 'Karyawan Test');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => $nama,
                'username' => $username,
                'password' => Hash::make($password),
                'status' => 'aktif',
                'role' => Roles::KARYAWAN,
            ]
        );

        RoleSync::sync($user, Roles::KARYAWAN);
    }
}

