<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleSync;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Buat akun superadmin default untuk development / bootstrap awal.
     *
     * Idempotent: jika user sudah ada, akan di-update agar aktif.
     */
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', env('TEST_SUPERADMIN_EMAIL', 'superadmin@test.com'));
        $username = env('SUPERADMIN_USERNAME', env('TEST_SUPERADMIN_USERNAME', 'superadmin.test'));
        $password = env('SUPERADMIN_PASSWORD', env('TEST_SUPERADMIN_PASSWORD', 'password'));
        $nama = env('SUPERADMIN_NAME', env('TEST_SUPERADMIN_NAME', 'Super Admin Test'));

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => $nama,
                'username' => $username,
                'password' => Hash::make($password),
                'status' => 'aktif',
                'role' => Roles::SUPER_ADMIN,
            ]
        );

        RoleSync::sync($user, Roles::SUPER_ADMIN);
    }
}
