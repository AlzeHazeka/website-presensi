<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $period['report_title'] ?? 'Rekap Presensi Bulanan' }}</title>
    <style>
        @page { margin: 28px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        .muted { color: #475569; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 12px; margin: 4px 0 0 0; }
        .header { border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px; }
        .brand { font-size: 12px; font-weight: 700; color: #0f172a; }
        .summary { width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; margin-top: 12px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px 0; }
        .summary-key { color: #475569; font-weight: 700; }
        .summary-value { text-align: right; font-weight: 800; }

        table.report { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.report th { background: #f8fafc; color: #334155; font-size: 8px; text-transform: uppercase; letter-spacing: .04em; text-align: left; padding: 6px 5px; border: 1px solid #e2e8f0; }
        table.report td { padding: 6px 5px; border: 1px solid #e2e8f0; vertical-align: top; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $period = $period ?? [];
        $periodLabel = $period['period_label'] ?? \Carbon\Carbon::createFromFormat('Y-m', "{$tahun}-{$bulan}")->translatedFormat('F Y');
        $title = $period['report_title'] ?? 'Rekap Presensi Bulanan';
    @endphp

    <div class="header">
        <div class="brand">{{ config('app.name', 'CV. Irfan Putera Sejahtera') }}</div>
        <p class="title">{{ $title }}</p>
        <p class="subtitle muted">Periode: <strong>{{ $periodLabel }}</strong></p>
        <p class="subtitle muted">Dibuat: {{ $generatedAt ?? '-' }} {{ $timezoneLabel ?? '' }}</p>

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td class="summary-key">Karyawan aktif</td>
                    <td class="summary-value">{{ $summary['totalKaryawanAktif'] ?? 0 }} / {{ $summary['totalKaryawan'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total presensi</td>
                    <td class="summary-value">{{ $summary['totalPresensi'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total izin/cuti</td>
                    <td class="summary-value">{{ $summary['totalIzin'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total lembur</td>
                    <td class="summary-value">{{ $summary['totalLembur'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total jam kerja</td>
                    <td class="summary-value">{{ $summary['totalJamKerjaText'] ?? '0 Jam' }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total jam lembur</td>
                    <td class="summary-value">{{ $summary['totalJamLemburText'] ?? '0 Jam' }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Rata-rata kehadiran (%)</td>
                    <td class="summary-value">{{ $summary['avgKehadiranPct'] ?? 0 }}%</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="report">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Divisi/Posisi</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-center">Hari Hadir</th>
                <th class="text-center">Izin/Cuti</th>
                <th class="text-center">Hari Lembur</th>
                <th class="text-center nowrap">Jam Kerja</th>
                <th class="text-center nowrap">Jam Lembur</th>
                <th class="text-center nowrap">Kehadiran %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap as $row)
                <tr>
                    <td>{{ $row['nama'] ?? '-' }}</td>
                    <td>{{ $row['posisi'] ?? '-' }}</td>
                    <td>{{ $row['role'] ?? '-' }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                    <td class="text-center">{{ $row['jumlah_presensi'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['jumlah_izin'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['jumlah_lembur'] ?? 0 }}</td>
                    <td class="text-center nowrap">{{ $row['total_jam_kerja_text'] ?? '0 Jam' }}</td>
                    <td class="text-center nowrap">{{ $row['total_jam_lembur_text'] ?? '0 Jam' }}</td>
                    <td class="text-center nowrap">{{ $row['kehadiran_pct'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center muted" style="padding: 18px 8px;">
                        Tidak ada data rekap presensi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $title }} • Halaman {PAGE_NUM} / {PAGE_COUNT}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(520, 820, "Halaman {PAGE_NUM} / {PAGE_COUNT}", null, 9, array(100,116,139));
        }
    </script>
</body>
</html>
