<?php

namespace App\Support;

use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyPresensiRekap
{
    /**
     * @return array{
     *   rekap: Collection<int, array{
     *     user_id:int,
     *     nama:?string,
     *     posisi:?string,
     *     role:?string,
     *     status:?string,
     *     jumlah_presensi:int,
     *     jumlah_izin:int,
     *     jumlah_lembur:int,
     *     kehadiran_pct:float
     *   }>,
     *   summary: array{
     *     daysInMonth:int,
     *     totalKaryawan:int,
     *     totalKaryawanAktif:int,
     *     totalPresensi:int,
     *     totalIzin:int,
     *     totalLembur:int,
     *     avgKehadiranPct:float
     *   }
     * }
     */
    public static function build(int $year, int $month): array
    {
        Carbon::setLocale('id');

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $users = User::orderBy('nama')->get(['user_id', 'nama', 'posisi', 'role', 'status']);
        $userIds = $users->pluck('user_id')->values();

        $presensiCounts = Presensi::whereIn('user_id', $userIds)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereNotNull('jam_masuk')
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $izinCounts = Izin::whereIn('user_id', $userIds)
            ->whereMonth('tanggal_izin', $month)
            ->whereYear('tanggal_izin', $year)
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $lemburCounts = Lembur::whereIn('user_id', $userIds)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $rekap = $users->map(function (User $user) use ($presensiCounts, $izinCounts, $lemburCounts, $daysInMonth) {
            $jumlahPresensi = (int) ($presensiCounts[$user->user_id] ?? 0);
            $jumlahIzin = (int) ($izinCounts[$user->user_id] ?? 0);
            $jumlahLembur = (int) ($lemburCounts[$user->user_id] ?? 0);

            $attendancePct = $daysInMonth > 0 ? round(($jumlahPresensi / $daysInMonth) * 100, 1) : 0.0;

            return [
                'user_id' => (int) $user->user_id,
                'nama' => $user->nama,
                'posisi' => $user->posisi,
                'role' => $user->role,
                'status' => $user->status,
                'jumlah_presensi' => $jumlahPresensi,
                'jumlah_izin' => $jumlahIzin,
                'jumlah_lembur' => $jumlahLembur,
                'kehadiran_pct' => $attendancePct,
            ];
        })->values();

        $summary = [
            'daysInMonth' => $daysInMonth,
            'totalKaryawan' => $users->count(),
            'totalKaryawanAktif' => $users->filter(fn (User $u) => strtolower((string) $u->status) === 'aktif')->count(),
            'totalPresensi' => (int) $rekap->sum('jumlah_presensi'),
            'totalIzin' => (int) $rekap->sum('jumlah_izin'),
            'totalLembur' => (int) $rekap->sum('jumlah_lembur'),
            'avgKehadiranPct' => $rekap->count() > 0 ? round($rekap->avg('kehadiran_pct'), 1) : 0.0,
        ];

        return [
            'rekap' => $rekap,
            'summary' => $summary,
        ];
    }
}

