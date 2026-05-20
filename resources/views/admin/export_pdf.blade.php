<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi Harian</title>
    <style>
        @page { margin: 28px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .muted { color: #475569; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 12px; margin: 4px 0 0 0; }
        .header { border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px; }
        .brand { font-size: 12px; font-weight: 700; color: #0f172a; }
        .meta { margin-top: 6px; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .pill { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
        .pill-success { background: #d1fae5; color: #065f46; }
        .pill-warning { background: #fef3c7; color: #92400e; }
        .pill-info { background: #e0f2fe; color: #0369a1; }
        .summary { width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; margin-top: 12px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px 0; }
        .summary-key { color: #475569; font-weight: 700; }
        .summary-value { text-align: right; font-weight: 800; }

        table.report { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.report th { background: #f8fafc; color: #334155; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; text-align: left; padding: 8px 8px; border: 1px solid #e2e8f0; }
        table.report td { padding: 8px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }

        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 10px; color: #64748b; }
        .footer .left { float: left; }
        .footer .right { float: right; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $dateHuman = \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
        $lemburUserIdSet = collect($lemburUserIds ?? [])->map(fn ($v) => (int) $v)->flip();

        $rows = collect($presensi ?? [])->map(function ($data) use ($lemburUserIdSet) {
            $diffInMinutes = $data->jam_masuk && $data->jam_keluar
                ? \Carbon\Carbon::parse($data->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar))
                : 0;
            $hours = (int) floor($diffInMinutes / 60);
            $minutes = (int) ($diffInMinutes % 60);

            $hadirLengkap = (bool) ($data->jam_masuk && $data->jam_keluar);
            $belumCheckout = (bool) ($data->jam_masuk && ! $data->jam_keluar);
            $status = $hadirLengkap ? ['label' => 'Hadir lengkap', 'tone' => 'success'] : ($belumCheckout ? ['label' => 'Belum checkout', 'tone' => 'warning'] : ['label' => 'Belum presensi', 'tone' => 'warning']);

            return [
                'nama' => $data->user?->nama ?? '-',
                'jam_masuk' => $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : '-',
                'lokasi_masuk' => $data->lokasi_masuk ?? 'Tidak Tersedia',
                'jam_keluar' => $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : '-',
                'lokasi_keluar' => $data->lokasi_keluar ?? 'Tidak Tersedia',
                'total_jam' => $diffInMinutes > 0 ? "{$hours} Jam {$minutes} Menit" : '-',
                'status' => $status,
                'has_lembur' => $lemburUserIdSet->has((int) $data->user_id),
            ];
        })->values();
    @endphp

    <div class="header">
        <div class="brand">{{ config('app.name', 'CV. Irfan Putera Sejahtera') }}</div>
        <p class="title">Laporan Presensi Harian</p>
        <p class="subtitle muted">Tanggal: <strong>{{ $dateHuman }}</strong></p>

        <table class="meta">
            <tr>
                <td class="muted" style="width: 110px;">Dibuat</td>
                <td>: {{ $generatedAt ?? '-' }} {{ $timezoneLabel ?? '' }}</td>
            </tr>
        </table>

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td class="summary-key">Total hadir</td>
                    <td class="summary-value">{{ $summary['total_hadir'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Belum checkout</td>
                    <td class="summary-value">{{ $summary['total_belum_checkout'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total izin/cuti</td>
                    <td class="summary-value">{{ $summary['total_izin_cuti'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-key">Total lembur</td>
                    <td class="summary-value">{{ $summary['total_lembur'] ?? 0 }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="report">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Status</th>
                <th>Jam Masuk</th>
                <th>Lokasi Masuk</th>
                <th>Jam Keluar</th>
                <th>Lokasi Keluar</th>
                <th>Total Jam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td class="nowrap">
                        @php $tone = $row['status']['tone'] ?? 'warning'; @endphp
                        <span class="pill {{ $tone === 'success' ? 'pill-success' : 'pill-warning' }}">
                            {{ $row['status']['label'] ?? 'Status' }}
                        </span>
                        @if ($row['has_lembur'])
                            <span class="pill pill-info">Lembur</span>
                        @endif
                    </td>
                    <td class="text-center nowrap">{{ $row['jam_masuk'] }}</td>
                    <td>{{ $row['lokasi_masuk'] }}</td>
                    <td class="text-center nowrap">{{ $row['jam_keluar'] }}</td>
                    <td>{{ $row['lokasi_keluar'] }}</td>
                    <td class="text-center nowrap">{{ $row['total_jam'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center muted" style="padding: 18px 8px;">
                        Tidak ada data presensi pada tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="left">Rekap Harian Presensi</div>
        <div class="right">Halaman <span class="pageNumber"></span> / <span class="totalPages"></span></div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(520, 820, "Halaman {PAGE_NUM} / {PAGE_COUNT}", null, 9, array(100,116,139));
        }
    </script>
</body>
</html>
