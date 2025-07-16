<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPresensiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $rekap;
    protected $bulan;
    protected $tahun;

    public function __construct($rekap, $bulan, $tahun)
    {
        $this->rekap = $rekap;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return collect($this->rekap)->map(function ($r) {
            return [
                $r['nama'],
                $r['role'],
                $r['status'],
                ucfirst($r['tipe_gaji']),
                $r['gaji'],
                $r['jumlah_presensi'],
                $r['jumlah_lembur'],
                $r['jumlah_izin'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Role',
            'Status',
            'Tipe Gaji',
            'Gaji / Hari',
            'Jumlah Presensi',
            'Jumlah Lembur',
            'Jumlah Izin',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}


