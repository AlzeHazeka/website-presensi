<?php

namespace App\Exports;

use App\Models\Presensi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class PresensiByDateExport implements FromView
{
    protected $tanggal;

    public function __construct($tanggal)
    {
        $this->tanggal = $tanggal;
    }

    public function view(): View
    {
        // Ambil semua presensi pada tanggal itu dengan relasi user
        $presensi = Presensi::whereDate('tanggal', $this->tanggal)
            ->with('user')
            ->orderBy('user_id')
            ->get();

        // Bangun baris data untuk Excel, sertakan kolom virtual total_jam
        $rows = $presensi->map(function($d) {
            if ($d->jam_masuk && $d->jam_keluar) {
                $diff    = Carbon::parse($d->jam_masuk)
                              ->diffInMinutes(Carbon::parse($d->jam_keluar));
                $totalJ  = floor($diff / 60) . 'j ' . ($diff % 60) . 'm';
            } else {
                $totalJ = '-';
            }
            return [
                'Nama'        => $d->user->nama,
                'Jam Masuk'   => $d->jam_masuk ? Carbon::parse($d->jam_masuk)->format('H:i') : '-',
                'Lokasi Masuk'=> $d->lokasi_masuk ?? '-',
                'Jam Keluar'  => $d->jam_keluar ? Carbon::parse($d->jam_keluar)->format('H:i') : '-',
                'Lokasi Keluar'=> $d->lokasi_keluar ?? '-',
                'Total Jam'   => $totalJ,
            ];
        });

        return view('exports.presensi_by_date_excel', [
            'rows'    => $rows,
            'tanggal' => $this->tanggal,
        ]);
    }
}
