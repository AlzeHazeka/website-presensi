<?php

namespace App\Services\Presensi;

use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyOperationalRecapService
{
    /**
     * @return array{
     *     tanggal:string,
     *     rows:array<int, array<string, mixed>>,
     *     summary:array<string, int>,
     *     total:int
     * }
     */
    public function build(string $tanggal): array
    {
        $date = Carbon::parse($tanggal)->toDateString();

        $presensiByUser = Presensi::whereDate('tanggal', $date)
            ->get()
            ->keyBy(fn (Presensi $item) => (int) $item->user_id);

        $izinByUser = Izin::whereDate('tanggal_izin', $date)
            ->get()
            ->filter(fn (Izin $item) => $this->isApprovedLeave($item))
            ->keyBy(fn (Izin $item) => (int) $item->user_id);

        $lemburByUser = Lembur::whereDate('tanggal', $date)
            ->get()
            ->keyBy(fn (Lembur $item) => (int) $item->user_id);

        $activityUserIds = collect()
            ->merge($presensiByUser->keys())
            ->merge($izinByUser->keys())
            ->merge($lemburByUser->keys())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = User::query()
            ->where(function ($query) use ($activityUserIds) {
                $query->where('status', 'aktif');

                if ($activityUserIds->isNotEmpty()) {
                    $query->orWhereIn('user_id', $activityUserIds->all());
                }
            })
            ->orderBy('nama')
            ->get(['user_id', 'nama', 'posisi', 'role', 'status']);

        $rows = $users
            ->map(fn (User $user) => $this->buildRow(
                $user,
                $presensiByUser->get((int) $user->user_id),
                $izinByUser->get((int) $user->user_id),
                $lemburByUser->get((int) $user->user_id),
            ))
            ->values();

        return [
            'tanggal' => $date,
            'rows' => $rows->all(),
            'summary' => $this->buildSummary($rows),
            'total' => $rows->count(),
        ];
    }

    private function buildRow(User $user, ?Presensi $presensi, ?Izin $izin, ?Lembur $lembur): array
    {
        if ($izin) {
            $leaveLabel = $this->leaveLabel($izin);

            return [
                'user_id' => (int) $user->user_id,
                'id_presensi' => null,
                'nama' => $user->nama,
                'status_hari_ini' => [
                    'key' => 'izin_cuti',
                    'label' => $leaveLabel,
                    'tone' => 'info',
                ],
                'has_lembur' => false,
                'jam_masuk' => null,
                'lokasi_masuk' => null,
                'jam_keluar' => null,
                'lokasi_keluar' => null,
                'total_jam_text' => '-',
                'total_minutes' => null,
                'lembur' => null,
                'leave' => [
                    'id_izin' => (int) $izin->id_izin,
                    'label' => $leaveLabel,
                    'keterangan' => $izin->keterangan,
                ],
            ];
        }

        $jamMasuk = $this->timeText($presensi?->getRawOriginal('jam_masuk') ?? $presensi?->jam_masuk);
        $jamKeluar = $this->timeText($presensi?->getRawOriginal('jam_keluar') ?? $presensi?->jam_keluar);
        $totalMinutes = $this->minutesBetween(
            $presensi?->getRawOriginal('jam_masuk') ?? $presensi?->jam_masuk,
            $presensi?->getRawOriginal('jam_keluar') ?? $presensi?->jam_keluar,
        );

        return [
            'user_id' => (int) $user->user_id,
            'id_presensi' => $presensi?->id_presensi ? (int) $presensi->id_presensi : null,
            'nama' => $user->nama,
            'status_hari_ini' => $this->attendanceStatus($jamMasuk, $jamKeluar),
            'has_lembur' => (bool) $lembur,
            'jam_masuk' => $jamMasuk,
            'lokasi_masuk' => $presensi?->lokasi_masuk,
            'jam_keluar' => $jamKeluar,
            'lokasi_keluar' => $presensi?->lokasi_keluar,
            'total_jam_text' => $this->durationText($totalMinutes),
            'total_minutes' => $totalMinutes,
            'lembur' => $lembur ? $this->overtimePayload($lembur) : null,
            'leave' => null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function buildSummary(Collection $rows): array
    {
        return [
            'total_hadir' => $rows->filter(fn (array $row) => in_array($row['status_hari_ini']['key'] ?? null, ['hadir_lengkap', 'belum_checkout'], true))->count(),
            'total_hadir_lengkap' => $rows->filter(fn (array $row) => ($row['status_hari_ini']['key'] ?? null) === 'hadir_lengkap')->count(),
            'total_belum_checkout' => $rows->filter(fn (array $row) => ($row['status_hari_ini']['key'] ?? null) === 'belum_checkout')->count(),
            'total_izin_cuti' => $rows->filter(fn (array $row) => ($row['status_hari_ini']['key'] ?? null) === 'izin_cuti')->count(),
            'total_lembur' => $rows->filter(fn (array $row) => (bool) ($row['has_lembur'] ?? false))->count(),
            'total_tidak_hadir' => $rows->filter(fn (array $row) => ($row['status_hari_ini']['key'] ?? null) === 'tidak_hadir')->count(),
        ];
    }

    private function attendanceStatus(?string $jamMasuk, ?string $jamKeluar): array
    {
        if ($jamMasuk && $jamKeluar) {
            return ['key' => 'hadir_lengkap', 'label' => 'Hadir lengkap', 'tone' => 'success'];
        }

        if ($jamMasuk && ! $jamKeluar) {
            return ['key' => 'belum_checkout', 'label' => 'Belum checkout', 'tone' => 'warning'];
        }

        return ['key' => 'tidak_hadir', 'label' => 'Tidak hadir', 'tone' => 'danger'];
    }

    private function overtimePayload(Lembur $lembur): array
    {
        $mulaiRaw = $lembur->getRawOriginal('jam_mulai_lembur') ?? $lembur->jam_mulai_lembur;
        $selesaiRaw = $lembur->getRawOriginal('jam_pulang_lembur') ?? $lembur->jam_pulang_lembur;
        $durationMinutes = $this->minutesBetween($mulaiRaw, $selesaiRaw);

        return [
            'id_lembur' => (int) $lembur->id_lembur,
            'jam_mulai' => $this->timeText($mulaiRaw),
            'jam_selesai' => $this->timeText($selesaiRaw),
            'durasi_text' => $this->durationText($durationMinutes, null),
            'duration_minutes' => $durationMinutes,
            'status' => $this->overtimeStatus($lembur),
        ];
    }

    private function isApprovedLeave(Izin $izin): bool
    {
        $status = $this->firstFilledAttribute($izin, ['status_approval', 'approval_status', 'status']);

        if ($status === null) {
            return true;
        }

        return in_array(strtolower($status), ['approved', 'approve', 'disetujui', 'accepted', 'diterima'], true);
    }

    private function leaveLabel(Izin $izin): string
    {
        $label = $this->firstFilledAttribute($izin, ['jenis_izin', 'tipe_izin', 'jenis', 'tipe', 'kategori']);

        if ($label === null) {
            return 'Izin/Cuti';
        }

        return $this->humanizeStatus($label);
    }

    private function overtimeStatus(Lembur $lembur): ?array
    {
        $status = $this->firstFilledAttribute($lembur, ['status_approval', 'approval_status', 'status']);

        if ($status === null) {
            return null;
        }

        $normalized = strtolower($status);
        $labels = [
            'pending' => ['label' => 'Pending', 'tone' => 'warning'],
            'menunggu' => ['label' => 'Pending', 'tone' => 'warning'],
            'approved' => ['label' => 'Disetujui', 'tone' => 'success'],
            'approve' => ['label' => 'Disetujui', 'tone' => 'success'],
            'disetujui' => ['label' => 'Disetujui', 'tone' => 'success'],
            'rejected' => ['label' => 'Ditolak', 'tone' => 'danger'],
            'reject' => ['label' => 'Ditolak', 'tone' => 'danger'],
            'ditolak' => ['label' => 'Ditolak', 'tone' => 'danger'],
        ];

        return [
            'value' => $status,
            ...($labels[$normalized] ?? ['label' => $this->humanizeStatus($status), 'tone' => 'slate']),
        ];
    }

    private function firstFilledAttribute(object $model, array $keys): ?string
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

    private function timeText(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function minutesBetween(mixed $start, mixed $end): ?int
    {
        if (! $start || ! $end) {
            return null;
        }

        try {
            return (int) round(Carbon::parse($start)->diffInMinutes(Carbon::parse($end)));
        } catch (\Throwable) {
            return null;
        }
    }

    private function durationText(?int $minutes, ?string $empty = '-'): ?string
    {
        if (! is_int($minutes) || $minutes <= 0) {
            return $empty;
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

    private function humanizeStatus(string $value): string
    {
        return str($value)
            ->replace(['_', '-'], ' ')
            ->title()
            ->toString();
    }
}
