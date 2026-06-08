<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiByDateExport implements FromArray, ShouldAutoSize, WithColumnWidths, WithStyles, WithTitle
{
    /**
     * @param  Collection<int, array<string, mixed>>  $presensi
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
            'Status Hari Ini',
            'Jam Masuk',
            'Lokasi Masuk',
            'Jam Keluar',
            'Lokasi Keluar',
            'Total Kerja',
            'Lembur',
        ];
    }

    public function title(): string
    {
        return 'Rekap Harian';
    }

    public function array(): array
    {
        Carbon::setLocale('id');

        $rows = [];

        $rows[] = ['Rekap Operasional Harian'];
        $rows[] = ['Tanggal', Carbon::parse($this->tanggal)->translatedFormat('d F Y')];
        $rows[] = ['Total Hadir', (string) ($this->summary['total_hadir'] ?? 0)];
        $rows[] = ['Belum Checkout', (string) ($this->summary['total_belum_checkout'] ?? 0)];
        $rows[] = ['Izin/Sakit/Cuti', (string) ($this->summary['total_izin_cuti'] ?? 0)];
        $rows[] = ['Lembur', (string) ($this->summary['total_lembur'] ?? 0)];
        $rows[] = [];

        $rows[] = $this->headings();

        $dataRows = $this->presensi
            ->map(function (array $item) {
                return [
                    $item['nama'] ?? '-',
                    $item['status_hari_ini']['label'] ?? '-',
                    $item['jam_masuk'] ?? '-',
                    $item['lokasi_masuk'] ?? '-',
                    $item['jam_keluar'] ?? '-',
                    $item['lokasi_keluar'] ?? '-',
                    $item['total_jam_text'] ?? '-',
                    $this->lemburText($item['lembur'] ?? null),
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
            'B' => 18,
            'C' => 12,
            'D' => 30,
            'E' => 12,
            'F' => 30,
            'G' => 16,
            'H' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Summary title
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Summary key/value alignment
        $sheet->getStyle('A2:A6')->getFont()->setBold(true);
        $sheet->getStyle('A2:B6')->getAlignment()->setHorizontal('left');

        // Headings row (after summary + blank row) -> row 8
        $headingRow = 8;
        $sheet->getStyle("A{$headingRow}:H{$headingRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headingRow}:H{$headingRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$headingRow}:H{$headingRow}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E2E8F0'); // slate-200

        $sheet->getStyle('B:B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D:D')->getAlignment()->setWrapText(true);
        $sheet->getStyle('F:F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H:H')->getAlignment()->setWrapText(true);

        // Freeze at first data row
        $sheet->freezePane('A9');

        return [];
    }

    private function lemburText(?array $lembur): string
    {
        if (! $lembur) {
            return '-';
        }

        $jamMulai = $lembur['jam_mulai'] ?? null;
        $jamSelesai = $lembur['jam_selesai'] ?? null;

        if (! $jamMulai) {
            return '-';
        }

        $lines = [
            $jamSelesai ? "{$jamMulai} - {$jamSelesai}" : "Mulai: {$jamMulai}",
        ];

        if (! empty($lembur['durasi_text'])) {
            $lines[] = $lembur['durasi_text'];
        }

        if (! empty($lembur['status']['label'])) {
            $lines[] = $lembur['status']['label'];
        }

        return implode("\n", $lines);
    }
}
