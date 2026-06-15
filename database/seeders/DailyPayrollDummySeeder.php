<?php

namespace Database\Seeders;

use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\User;
use App\Support\RoleSync;
use App\Support\Roles;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DailyPayrollDummySeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) env('PAYROLL_DUMMY_YEAR', now('Asia/Jakarta')->year);
        $month = 6;

        $user = User::updateOrCreate(
            ['email' => env('PAYROLL_DUMMY_EMAIL', 'gaji.harian@test.com')],
            [
                'nik' => env('PAYROLL_DUMMY_NIK', '00099'),
                'nama' => env('PAYROLL_DUMMY_NAME', 'Karyawan Harian Dummy'),
                'username' => env('PAYROLL_DUMMY_USERNAME', 'gaji.harian.test'),
                'password' => Hash::make(env('PAYROLL_DUMMY_PASSWORD', 'password')),
                'alamat' => 'Data dummy untuk pengujian hitung gaji harian.',
                'telepon' => '080000000099',
                'posisi' => 'Operator Harian',
                'tanggal_lahir' => '1998-06-01',
                'tanggal_masuk' => "{$year}-06-01",
                'gaji' => null,
                'tipe_gaji' => 'harian',
                'status' => 'aktif',
                'role' => Roles::KARYAWAN,
            ],
        );

        RoleSync::sync($user, Roles::KARYAWAN);

        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $endDate = $startDate->copy()->endOfMonth();
        $attendancePatterns = [
            ['08:00:00', '16:00:00'], // pas 8 jam
            ['08:00:00', '15:45:00'], // kurang sedikit: 7 jam 45 menit
            ['08:00:00', '16:15:00'], // lebih sedikit: 8 jam 15 menit
            ['08:00:00', '14:00:00'], // kurang banyak: 6 jam
            ['08:00:00', '18:00:00'], // lebih banyak: 10 jam
            ['08:30:00', '16:00:00'], // kurang sedang: 7 jam 30 menit
            ['07:30:00', '16:30:00'], // lebih sedang: 9 jam
        ];
        $overtimePatterns = [
            2 => ['16:15:00', '17:15:00'], // 1 jam
            5 => ['18:15:00', '20:15:00'], // 2 jam
            9 => ['16:30:00', '19:45:00'], // 3 jam 15 menit
            13 => ['14:30:00', '18:45:00'], // 4 jam 15 menit
            18 => ['16:15:00', '21:15:00'], // 5 jam
            23 => ['16:30:00', '17:50:00'], // 1 jam 20 menit
            27 => ['18:15:00', '22:55:00'], // 4 jam 40 menit
        ];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $tanggal = $date->toDateString();
            [$jamMasuk, $jamKeluar] = $attendancePatterns[($date->day - 1) % count($attendancePatterns)];

            Presensi::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'tanggal' => $tanggal,
                ],
                [
                    'jam_masuk' => "{$tanggal} {$jamMasuk}",
                    'lokasi_masuk' => 'Kantor IPS',
                    'jam_keluar' => "{$tanggal} {$jamKeluar}",
                    'lokasi_keluar' => 'Kantor IPS',
                    'lat_masuk' => null,
                    'lng_masuk' => null,
                    'accuracy_masuk' => null,
                    'ip_masuk' => '127.0.0.1',
                    'ua_masuk' => 'Seeder DailyPayrollDummySeeder',
                    'lat_keluar' => null,
                    'lng_keluar' => null,
                    'accuracy_keluar' => null,
                    'ip_keluar' => '127.0.0.1',
                    'ua_keluar' => 'Seeder DailyPayrollDummySeeder',
                ],
            );

            if (! array_key_exists($date->day, $overtimePatterns)) {
                continue;
            }

            [$jamMulaiLembur, $jamPulangLembur] = $overtimePatterns[$date->day];

            Lembur::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'tanggal' => $tanggal,
                ],
                [
                    'jam_mulai_lembur' => "{$tanggal} {$jamMulaiLembur}",
                    'lokasi_mulai_lembur' => 'Kantor IPS',
                    'lat_mulai_lembur' => null,
                    'lng_mulai_lembur' => null,
                    'accuracy_mulai_lembur' => null,
                    'ip_mulai_lembur' => '127.0.0.1',
                    'ua_mulai_lembur' => 'Seeder DailyPayrollDummySeeder',
                    'jam_pulang_lembur' => "{$tanggal} {$jamPulangLembur}",
                    'lokasi_pulang_lembur' => 'Kantor IPS',
                    'lat_selesai_lembur' => null,
                    'lng_selesai_lembur' => null,
                    'accuracy_selesai_lembur' => null,
                    'ip_selesai_lembur' => '127.0.0.1',
                    'ua_selesai_lembur' => 'Seeder DailyPayrollDummySeeder',
                ],
            );
        }
    }
}
