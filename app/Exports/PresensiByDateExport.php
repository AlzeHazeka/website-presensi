<?php

namespace App\Exports;

use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiByDateExport implements FromArray, WithStyles, WithColumnWidths
{
    /**
     * @param  Collection<int, Presensi>  $presensi
     * @param  array{total_hadir:int, total_belum_checkout:int, total_izin_cuti:int, total_lembur:int}  $summary
     */
    public function __construct(
        private readonly Collection $presensi,
        private readonly string $tanggal,
        private readonly array $summary,
    ) {
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Jam Masuk',
            'Lokasi Masuk',
            'Jam Keluar',
            'Lokasi Keluar',
            'Total Jam',
        ];
    }

    public function array(): array
    {
        Carbon::setLocale('id');

        $rows = [];

        $rows[] = ['Laporan Presensi Harian'];
        $rows[] = ['Tanggal', Carbon::parse($this->tanggal)->translatedFormat('d F Y')];
        $rows[] = ['Total hadir', (string) ($this->summary['total_hadir'] ?? 0)];
        $rows[] = ['Belum checkout', (string) ($this->summary['total_belum_checkout'] ?? 0)];
        $rows[] = ['Total izin/cuti', (string) ($this->summary['total_izin_cuti'] ?? 0)];
        $rows[] = ['Total lembur', (string) ($this->summary['total_lembur'] ?? 0)];
        $rows[] = [];

        $rows[] = $this->headings();

        $dataRows = $this->presensi
            ->map(function (Presensi $item) {
                $diffInMinutes = $item->jam_masuk && $item->jam_keluar
                    ? Carbon::parse($item->jam_masuk)->diffInMinutes(Carbon::parse($item->jam_keluar))
                    : 0;

                $hours = (int) floor($diffInMinutes / 60);
                $minutes = (int) ($diffInMinutes % 60);

                return [
                    $item->user?->nama ?? '-',
                    $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : 'Belum Presensi',
                    $item->lokasi_masuk ?? 'Tidak Tersedia',
                    $item->jam_keluar ? Carbon::parse($item->jam_keluar)->format('H:i') : 'Belum Presensi',
                    $item->lokasi_keluar ?? 'Tidak Tersedia',
                    $diffInMinutes > 0 ? "{$hours} Jam {$minutes} Menit" : '-',
                ];
            })
            ->values()
            ->all();

        return array_merge($rows, $dataRows);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 12,
            'C' => 38,
            'D' => 12,
            'E' => 38,
            'F' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Summary title
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Summary key/value alignment
        $sheet->getStyle('A2:A6')->getFont()->setBold(true);
        $sheet->getStyle('A2:B6')->getAlignment()->setHorizontal('left');

        // Headings row (after summary + blank row) -> row 8
        $headingRow = 8;
        $sheet->getStyle("A{$headingRow}:F{$headingRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headingRow}:F{$headingRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$headingRow}:F{$headingRow}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E2E8F0'); // slate-200

        // Wrap location columns
        $sheet->getStyle('C:C')->getAlignment()->setWrapText(true);
        $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);

        // Freeze at first data row
        $sheet->freezePane('A9');

        return [];
    }
}
