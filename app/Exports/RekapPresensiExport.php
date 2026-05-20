<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RekapPresensiExport implements FromView
{
    protected $rekap;
    protected $bulan;
    protected $tahun;
    protected $summary;

    public function __construct($rekap, $bulan, $tahun, $summary = null)
    {
        $this->rekap = $rekap;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->summary = $summary;
    }

    public function view(): View
    {
        return view('exports.rekap-presensi', [
            'rekap' => $this->rekap,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'summary' => $this->summary,
        ]);
    }
}
