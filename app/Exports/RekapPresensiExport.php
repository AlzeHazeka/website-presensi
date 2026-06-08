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
    protected $period;

    public function __construct($rekap, $bulan, $tahun, $summary = null, $period = null)
    {
        $this->rekap = $rekap;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->summary = $summary;
        $this->period = $period;
    }

    public function view(): View
    {
        return view('exports.rekap-presensi', [
            'rekap' => $this->rekap,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'summary' => $this->summary,
            'period' => $this->period,
        ]);
    }
}
