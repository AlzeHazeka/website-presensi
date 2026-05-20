<?php

namespace App\Support;

use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Presensi;
use Carbon\Carbon;

class IzinEligibility
{
    /**
     * @return array{
     *   date:string,
     *   has_presensi:bool,
     *   has_lembur:bool,
     *   has_izin:bool,
     *   blocked_by_activity:bool,
     *   activity_message:?string
     * }
     */
    public static function check(int $userId, string $date): array
    {
        $dateString = Carbon::parse($date)->toDateString();

        $hasPresensi = Presensi::where('user_id', $userId)
            ->whereDate('tanggal', $dateString)
            ->exists();

        $hasLembur = Lembur::where('user_id', $userId)
            ->whereDate('tanggal', $dateString)
            ->exists();

        $hasIzin = Izin::where('user_id', $userId)
            ->whereDate('tanggal_izin', $dateString)
            ->exists();

        $blockedByActivity = $hasPresensi || $hasLembur;

        return [
            'date' => $dateString,
            'has_presensi' => $hasPresensi,
            'has_lembur' => $hasLembur,
            'has_izin' => $hasIzin,
            'blocked_by_activity' => $blockedByActivity,
            'activity_message' => $blockedByActivity
                ? 'Anda sudah memiliki aktivitas presensi/lembur pada tanggal ini. Pengajuan izin/cuti tidak tersedia.'
                : null,
        ];
    }

    /**
     * @return array<int, array{id_izin:int, tanggal_izin:string, tanggal_pengajuan:string, keterangan:?string}>
     */
    public static function monthlyHistory(int $userId, int $year, int $month, string $timezone): array
    {
        return Izin::where('user_id', $userId)
            ->whereYear('tanggal_izin', $year)
            ->whereMonth('tanggal_izin', $month)
            ->orderBy('tanggal_izin', 'asc')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get()
            ->map(function (Izin $izin) use ($timezone) {
                $tanggalIzin = Carbon::parse($izin->tanggal_izin)->toDateString();
                $tanggalPengajuan = Carbon::parse($izin->tanggal_pengajuan)->timezone($timezone)->toIso8601String();

                return [
                    'id_izin' => (int) $izin->id_izin,
                    'tanggal_izin' => $tanggalIzin,
                    'tanggal_pengajuan' => $tanggalPengajuan,
                    'keterangan' => $izin->keterangan ? (string) $izin->keterangan : null,
                ];
            })
            ->values()
            ->all();
    }
}
