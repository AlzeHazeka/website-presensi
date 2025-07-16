<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class LemburByUserExport implements FromView
{
    protected $user, $lembur, $bulan, $tahun;

    public function __construct($user, $lembur, $bulan, $tahun)
    {
        $this->user = $user;
        $this->lembur = $lembur;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        return view('exports.lembur_by_user_excel', [
            'user' => $this->user,
            'lembur' => $this->lembur,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ]);
    }
}
