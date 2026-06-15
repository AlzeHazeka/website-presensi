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
     *     total_jam_kerja_minutes:int,
     *     total_jam_kerja_text:string,
     *     total_jam_lembur_minutes:int,
     *     total_jam_lembur_text:string,
     *     kehadiran_pct:float
     *   }>,
     *   summary: array{
     *     daysInMonth:int,
     *     totalKaryawan:int,
     *     totalKaryawanAktif:int,
     *     totalPresensi:int,
     *     totalIzin:int,
     *     totalLembur:int,
     *     totalJamKerjaMinutes:int,
     *     totalJamKerjaText:string,
     *     totalJamLemburMinutes:int,
     *     totalJamLemburText:string,
     *     avgKehadiranPct:float
     *   }
     * }
     */
    public static function build(int $year, int $month, bool $includeInactive = false): array
    {
        Carbon::setLocale('id');

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->startOfDay();

        return self::buildForRange($startDate->toDateString(), $endDate->toDateString(), $includeInactive);
    }

    public static function buildForRange(string $startDate, string $endDate, bool $includeInactive = false): array
    {
        Carbon::setLocale('id');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $daysInPeriod = (int) $start->diffInDays($end) + 1;

        $users = User::query()
            ->when(! $includeInactive, fn ($query) => $query->where('status', 'aktif'))
            ->orderBy('nama')
            ->get(['user_id', 'nama', 'posisi', 'role', 'status']);
        $userIds = $users->pluck('user_id')->values();

        $presensiByUser = Presensi::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Presensi $item) => (int) $item->user_id);

        $izinByUser = Izin::whereIn('user_id', $userIds)
            ->whereBetween('tanggal_izin', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->filter(fn (Izin $item) => self::isApprovedLeave($item))
            ->groupBy(fn (Izin $item) => (int) $item->user_id);

        $lemburByUser = Lembur::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Lembur $item) => (int) $item->user_id);

        $rekap = $users->map(function (User $user) use ($presensiByUser, $izinByUser, $lemburByUser, $daysInPeriod) {
            $presensiItems = $presensiByUser->get((int) $user->user_id, collect());
            $izinItems = $izinByUser->get((int) $user->user_id, collect());
            $lemburItems = $lemburByUser->get((int) $user->user_id, collect());

            $jumlahPresensi = $presensiItems->filter(fn (Presensi $item) => filled($item->getRawOriginal('jam_masuk') ?? $item->jam_masuk))->count();
            $jumlahIzin = $izinItems->count();
            $jumlahLembur = $lemburItems->count();
            $totalKerjaMinutes = $presensiItems->sum(fn (Presensi $item) => self::minutesBetween(
                $item->getRawOriginal('jam_masuk') ?? $item->jam_masuk,
                $item->getRawOriginal('jam_keluar') ?? $item->jam_keluar,
            ));
            $totalLemburMinutes = $lemburItems->sum(fn (Lembur $item) => self::minutesBetween(
                $item->getRawOriginal('jam_mulai_lembur') ?? $item->jam_mulai_lembur,
                $item->getRawOriginal('jam_pulang_lembur') ?? $item->jam_pulang_lembur,
            ));

            $attendancePct = $daysInPeriod > 0 ? round(($jumlahPresensi / $daysInPeriod) * 100, 1) : 0.0;

            return [
                'user_id' => (int) $user->user_id,
                'nama' => $user->nama,
                'posisi' => $user->posisi,
                'role' => $user->role,
                'status' => $user->status,
                'jumlah_presensi' => $jumlahPresensi,
                'jumlah_izin' => $jumlahIzin,
                'jumlah_lembur' => $jumlahLembur,
                'total_jam_kerja_minutes' => $totalKerjaMinutes,
                'total_jam_kerja_text' => self::durationText($totalKerjaMinutes),
                'total_jam_lembur_minutes' => $totalLemburMinutes,
                'total_jam_lembur_text' => self::durationText($totalLemburMinutes),
                'kehadiran_pct' => $attendancePct,
            ];
        })->values();

        $totalJamKerjaMinutes = (int) $rekap->sum('total_jam_kerja_minutes');
        $totalJamLemburMinutes = (int) $rekap->sum('total_jam_lembur_minutes');

        $summary = [
            'daysInMonth' => $daysInPeriod,
            'daysInPeriod' => $daysInPeriod,
            'totalKaryawan' => $users->count(),
            'totalKaryawanAktif' => $users->filter(fn (User $u) => strtolower((string) $u->status) === 'aktif')->count(),
            'includeInactive' => $includeInactive,
            'totalPresensi' => (int) $rekap->sum('jumlah_presensi'),
            'totalIzin' => (int) $rekap->sum('jumlah_izin'),
            'totalLembur' => (int) $rekap->sum('jumlah_lembur'),
            'totalJamKerjaMinutes' => $totalJamKerjaMinutes,
            'totalJamKerjaText' => self::durationText($totalJamKerjaMinutes),
            'totalJamLemburMinutes' => $totalJamLemburMinutes,
            'totalJamLemburText' => self::durationText($totalJamLemburMinutes),
            'avgKehadiranPct' => $rekap->count() > 0 ? round($rekap->avg('kehadiran_pct'), 1) : 0.0,
        ];

        return [
            'rekap' => $rekap,
            'summary' => $summary,
        ];
    }

    private static function isApprovedLeave(Izin $izin): bool
    {
        $status = self::firstFilledAttribute($izin, ['status_approval', 'approval_status', 'status']);

        if ($status === null) {
            return true;
        }

        return in_array(strtolower($status), ['approved', 'approve', 'disetujui', 'accepted', 'diterima'], true);
    }

    private static function firstFilledAttribute(object $model, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $model->{$key} ?? null;

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            return trim((string) $value);
        }

        return null;
    }

    private static function minutesBetween(mixed $start, mixed $end): int
    {
        if (! $start || ! $end) {
            return 0;
        }

        try {
            return max(0, (int) round(Carbon::parse($start)->diffInMinutes(Carbon::parse($end))));
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function durationText(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 Jam';
        }

        $hours = (int) floor($minutes / 60);
        $remainingMinutes = (int) ($minutes % 60);

        if ($remainingMinutes === 0) {
            return "{$hours} Jam";
        }

        if ($hours === 0) {
            return "{$remainingMinutes} Menit";
        }

        return "{$hours} Jam {$remainingMinutes} Menit";
    }
}
