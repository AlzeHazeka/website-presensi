<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Operasional Harian</title>
    <style>
        @page { margin: 24px 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        .muted { color: #475569; }
        .title { font-size: 15px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 11px; margin: 4px 0 0 0; }
        .header { border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 12px; }
        .brand { font-size: 12px; font-weight: 700; color: #0f172a; }
        .meta { margin-top: 6px; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .pill { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 8px; font-weight: 700; }
        .pill-success { background: #d1fae5; color: #065f46; }
        .pill-warning { background: #fef3c7; color: #92400e; }
        .pill-info { background: #e0f2fe; color: #0369a1; }
        .summary { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; margin-top: 10px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px 0; }
        .summary-key { color: #475569; font-weight: 700; }
        .summary-value { text-align: right; font-weight: 800; }

        table.report { width: 100%; border-collapse: collapse; margin-top: 12px; table-layout: fixed; }
        table.report th { background: #f8fafc; color: #334155; font-size: 7.5px; text-transform: uppercase; letter-spacing: .03em; text-align: left; padding: 5px 4px; border: 1px solid #e2e8f0; }
        table.report td { padding: 5px 4px; border: 1px solid #e2e8f0; vertical-align: top; line-height: 1.35; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .col-name { width: 16%; }
        .col-status { width: 13%; }
        .col-time { width: 7%; }
        .col-location { width: 15%; }
        .col-total { width: 9%; }
        .col-lembur { width: 18%; }
        .small { font-size: 8px; }

        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 10px; color: #64748b; }
        .footer .left { float: left; }
        .footer .right { float: right; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $dateHuman = \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
        $rows = collect($presensi ?? [])->values();
        $compactLocation = function ($value) {
            $value = trim((string) ($value ?? ''));
            if ($value === '') return '-';
            return \Illuminate\Support\Str::limit($value, 42, '...');
        };
        $lemburText = function ($lembur) {
            if (! $lembur) return '-';
            $mulai = $lembur['jam_mulai'] ?? null;
            $selesai = $lembur['jam_selesai'] ?? null;
            if (! $mulai) return '-';
            $lines = [$selesai ? "{$mulai} - {$selesai}" : "Mulai: {$mulai}"];
            if (! empty($lembur['durasi_text'])) $lines[] = $lembur['durasi_text'];
            if (! empty($lembur['status']['label'])) $lines[] = $lembur['status']['label'];
            return implode('<br>', array_map('e', $lines));
        };
    @endphp

    <div class="header">
        <div class="brand">{{ config('app.name', 'CV. Irfan Putera Sejahtera') }}</div>
        <p class="title">Rekap Operasional Harian</p>
        <p class="subtitle muted">Tanggal rekap: <strong>{{ $dateHuman }}</strong></p>

        <table class="meta">
            <tr>
                <td class="muted" style="width: 110px;">Dibuat</td>
                <td>: {{ $generatedAt ?? '-' }} {{ $timezoneLabel ?? '' }}</td>
            </tr>
        </table>

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td class="summary-key">Total Hadir</td>
                    <td class="summary-value">{{ $summary['total_hadir'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Belum Checkout</td>
                    <td class="summary-value">{{ $summary['total_belum_checkout'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Izin/Sakit/Cuti</td>
                    <td class="summary-value">{{ $summary['total_izin_cuti'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Lembur</td>
                    <td class="summary-value">{{ $summary['total_lembur'] ?? 0 }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="report">
        <thead>
            <tr>
                <th class="col-name">Nama</th>
                <th class="col-status">Status Hari Ini</th>
                <th class="col-time">Jam Masuk</th>
                <th class="col-location">Lokasi Masuk</th>
                <th class="col-time">Jam Keluar</th>
                <th class="col-location">Lokasi Keluar</th>
                <th class="col-total">Total Kerja</th>
                <th class="col-lembur">Lembur</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['nama'] ?? '-' }}</td>
                    <td class="nowrap">
                        @php $tone = $row['status_hari_ini']['tone'] ?? 'warning'; @endphp
                        <span class="pill {{ $tone === 'success' ? 'pill-success' : ($tone === 'info' ? 'pill-info' : 'pill-warning') }}">
                            {{ $row['status_hari_ini']['label'] ?? 'Status' }}
                        </span>
                    </td>
                    <td class="text-center nowrap">{{ $row['jam_masuk'] ?? '-' }}</td>
                    <td class="small">{{ $compactLocation($row['lokasi_masuk'] ?? null) }}</td>
                    <td class="text-center nowrap">{{ $row['jam_keluar'] ?? '-' }}</td>
                    <td class="small">{{ $compactLocation($row['lokasi_keluar'] ?? null) }}</td>
                    <td class="text-center nowrap">{{ $row['total_jam_text'] ?? '-' }}</td>
                    <td class="small">{!! $lemburText($row['lembur'] ?? null) !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center muted" style="padding: 18px 8px;">
                        Tidak ada data presensi pada tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="left">Rekap Operasional Harian</div>
        <div class="right">Halaman <span class="pageNumber"></span> / <span class="totalPages"></span></div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(520, 820, "Halaman {PAGE_NUM} / {PAGE_COUNT}", null, 9, array(100,116,139));
        }
    </script>
</body>
</html>
