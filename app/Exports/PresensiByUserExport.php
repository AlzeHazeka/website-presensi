<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Presensi;
use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PresensiByUserExport implements FromView
{
    protected $user, $presensi, $izin, $bulan, $tahun;

    public function __construct($user, $presensi, $izin, $bulan, $tahun)
    {
        $this->user = $user;
        $this->presensi = $presensi;
        $this->izin = $izin;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $jumlahHari = \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->daysInMonth;
        $rows = [];

        for ($i = 1; $i <= $jumlahHari; $i++) {
            $tanggalCarbon = \Carbon\Carbon::create($this->tahun, $this->bulan, $i);
            $tglFormat = $tanggalCarbon->format('Y-m-d');
            $tglTampil = $tanggalCarbon->format('d M Y');
            $hari = $tanggalCarbon->translatedFormat('l');

            $dPresensi = $this->presensi[$tglFormat] ?? null;
            $dIzin = $this->izin[$tglFormat] ?? null;

            $status = 'Tidak Ada Data';
            if ($dPresensi) {
                $status = 'Presensi';
            } elseif ($dIzin) {
                $status = 'Izin ' . $dIzin->izin;
            }

            $jamMasuk = $dPresensi?->jam_masuk ? \Carbon\Carbon::parse($dPresensi->jam_masuk)->format('H:i') : '-';
            $jamKeluar = $dPresensi?->jam_keluar ? \Carbon\Carbon::parse($dPresensi->jam_keluar)->format('H:i') : '-';

            $totalJ = ($dPresensi && $dPresensi->jam_masuk && $dPresensi->jam_keluar)
                ? \Carbon\Carbon::parse($dPresensi->jam_masuk)
                    ->diff(\Carbon\Carbon::parse($dPresensi->jam_keluar))
                    ->format('%H Jam %I Menit')
                : '-';

            $rows[] = [
                'tanggal' => $tglTampil,
                'hari' => $hari,
                'status' => $status,
                'jam_masuk' => $jamMasuk,
                'jam_keluar' => $jamKeluar,
                'total_jam' => $totalJ,
            ];
        }

        return view('exports.presensi_by_user_excel', [
            'user' => $this->user,
            'rows' => $rows,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ]);
    }
}

