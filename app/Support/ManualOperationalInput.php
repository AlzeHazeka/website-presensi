<?php

namespace App\Support;

use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Presensi;
use Carbon\Carbon;

class ManualOperationalInput
{
    /**
     * @param  array{
     *   user_id:int,
     *   tanggal:string,
     *   jam_masuk:string,
     *   jam_keluar:string,
     *   lokasi_masuk:?string,
     *   lokasi_keluar:?string,
     *   latitude_masuk?:float|null,
     *   longitude_masuk?:float|null,
     *   accuracy_masuk?:int|null,
     *   latitude_keluar?:float|null,
     *   longitude_keluar?:float|null,
     *   accuracy_keluar?:int|null,
     *   ip_masuk?:?string,
     *   ua_masuk?:?string,
     *   ip_keluar?:?string,
     *   ua_keluar?:?string
     * } $data
     */
    public static function createPresensi(array $data, string $timezone): void
    {
        $userId = (int) $data['user_id'];
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        $hasIzin = Izin::where('user_id', $userId)->whereDate('tanggal_izin', $tanggal)->exists();
        if ($hasIzin) {
            throw new \RuntimeException('Tidak bisa input presensi: karyawan sedang izin/cuti pada tanggal tersebut.');
        }

        $existing = Presensi::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();
        if ($existing) {
            throw new \RuntimeException('Presensi untuk karyawan ini di tanggal tersebut sudah ada.');
        }

        $jamMasuk = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_masuk']}", $timezone)->seconds(0);
        $jamKeluar = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_keluar']}", $timezone)->seconds(0);

        Presensi::create([
            'user_id' => $userId,
            'tanggal' => $tanggal,
            'jam_masuk' => $jamMasuk,
            'lokasi_masuk' => $data['lokasi_masuk'] ?? null,
            'lat_masuk' => $data['latitude_masuk'] ?? null,
            'lng_masuk' => $data['longitude_masuk'] ?? null,
            'accuracy_masuk' => $data['accuracy_masuk'] ?? null,
            'ip_masuk' => $data['ip_masuk'] ?? null,
            'ua_masuk' => $data['ua_masuk'] ?? null,
            'jam_keluar' => $jamKeluar,
            'lokasi_keluar' => $data['lokasi_keluar'] ?? null,
            'lat_keluar' => $data['latitude_keluar'] ?? null,
            'lng_keluar' => $data['longitude_keluar'] ?? null,
            'accuracy_keluar' => $data['accuracy_keluar'] ?? null,
            'ip_keluar' => $data['ip_keluar'] ?? null,
            'ua_keluar' => $data['ua_keluar'] ?? null,
        ]);
    }

    /**
     * @param  array{user_id:int, tanggal:string, keterangan?:?string}  $data
     */
    public static function createIzin(array $data, string $timezone): void
    {
        $userId = (int) $data['user_id'];
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        $hasPresensi = Presensi::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();
        $hasLembur = Lembur::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();
        if ($hasPresensi || $hasLembur) {
            throw new \RuntimeException('Tidak bisa input izin/cuti: sudah ada presensi/lembur pada tanggal tersebut.');
        }

        $existing = Izin::where('user_id', $userId)->whereDate('tanggal_izin', $tanggal)->exists();
        if ($existing) {
            throw new \RuntimeException('Izin/cuti untuk karyawan ini di tanggal tersebut sudah ada.');
        }

        Izin::create([
            'user_id' => $userId,
            'tanggal_pengajuan' => Carbon::now($timezone),
            'tanggal_izin' => $tanggal,
            'keterangan' => isset($data['keterangan']) ? (string) $data['keterangan'] : null,
        ]);
    }

    /**
     * @param  array{
     *   user_id:int,
     *   tanggal:string,
     *   jam_mulai:string,
     *   jam_selesai:string,
     *   lokasi_mulai:?string,
     *   lokasi_selesai:?string,
     *   latitude_mulai?:float|null,
     *   longitude_mulai?:float|null,
     *   accuracy_mulai?:int|null,
     *   latitude_selesai?:float|null,
     *   longitude_selesai?:float|null,
     *   accuracy_selesai?:int|null,
     *   ip_mulai?:?string,
     *   ua_mulai?:?string,
     *   ip_selesai?:?string,
     *   ua_selesai?:?string
     * } $data
     */
    public static function createLembur(array $data, string $timezone): void
    {
        $userId = (int) $data['user_id'];
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        $hasIzin = Izin::where('user_id', $userId)->whereDate('tanggal_izin', $tanggal)->exists();
        if ($hasIzin) {
            throw new \RuntimeException('Tidak bisa input lembur: karyawan sedang izin/cuti pada tanggal tersebut.');
        }

        $existing = Lembur::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();
        if ($existing) {
            throw new \RuntimeException('Lembur untuk karyawan ini di tanggal tersebut sudah ada.');
        }

        $mulai = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_mulai']}", $timezone)->seconds(0);
        $selesai = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_selesai']}", $timezone)->seconds(0);

        Lembur::create([
            'user_id' => $userId,
            'tanggal' => $tanggal,
            'jam_mulai_lembur' => $mulai,
            'lokasi_mulai_lembur' => $data['lokasi_mulai'] ?? null,
            'lat_mulai_lembur' => $data['latitude_mulai'] ?? null,
            'lng_mulai_lembur' => $data['longitude_mulai'] ?? null,
            'accuracy_mulai_lembur' => $data['accuracy_mulai'] ?? null,
            'ip_mulai_lembur' => $data['ip_mulai'] ?? null,
            'ua_mulai_lembur' => $data['ua_mulai'] ?? null,
            'jam_pulang_lembur' => $selesai,
            'lokasi_pulang_lembur' => $data['lokasi_selesai'] ?? null,
            'lat_selesai_lembur' => $data['latitude_selesai'] ?? null,
            'lng_selesai_lembur' => $data['longitude_selesai'] ?? null,
            'accuracy_selesai_lembur' => $data['accuracy_selesai'] ?? null,
            'ip_selesai_lembur' => $data['ip_selesai'] ?? null,
            'ua_selesai_lembur' => $data['ua_selesai'] ?? null,
        ]);
    }
}

