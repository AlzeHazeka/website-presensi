@php
    \Carbon\Carbon::setLocale('id');
    $period = $period ?? [];
    $periodLabel = $period['period_label'] ?? \Carbon\Carbon::createFromFormat('Y-m', "{$tahun}-{$bulan}")->translatedFormat('F Y');
    $title = $period['report_title'] ?? 'Rekap Presensi Bulanan';
@endphp

<table>
    <tbody>
        <tr>
            <td><strong>{{ $title }}</strong></td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ $periodLabel }}</td>
        </tr>
        @if (!empty($summary))
            <tr>
                <td>Total karyawan</td>
                <td>{{ $summary['totalKaryawan'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Karyawan aktif</td>
                <td>{{ $summary['totalKaryawanAktif'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total presensi</td>
                <td>{{ $summary['totalPresensi'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total izin/cuti</td>
                <td>{{ $summary['totalIzin'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total lembur</td>
                <td>{{ $summary['totalLembur'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total jam kerja</td>
                <td>{{ $summary['totalJamKerjaText'] ?? '0 Jam' }}</td>
            </tr>
            <tr>
                <td>Total jam lembur</td>
                <td>{{ $summary['totalJamLemburText'] ?? '0 Jam' }}</td>
            </tr>
            <tr>
                <td>Rata-rata kehadiran (%)</td>
                <td>{{ $summary['avgKehadiranPct'] ?? 0 }}</td>
            </tr>
        @endif
        <tr><td></td></tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Divisi/Posisi</th>
            <th>Role</th>
            <th>Status</th>
            <th>Hari Hadir</th>
            <th>Jumlah Izin/Cuti</th>
            <th>Hari Lembur</th>
            <th>Total Jam Kerja</th>
            <th>Total Jam Lembur</th>
            <th>Kehadiran (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekap as $r)
            <tr>
                <td>{{ $r['nama'] }}</td>
                <td>{{ $r['posisi'] ?? '-' }}</td>
                <td>{{ $r['role'] ?? '-' }}</td>
                <td>{{ $r['status'] ?? '-' }}</td>
                <td>{{ $r['jumlah_presensi'] ?? 0 }}</td>
                <td>{{ $r['jumlah_izin'] ?? 0 }}</td>
                <td>{{ $r['jumlah_lembur'] ?? 0 }}</td>
                <td>{{ $r['total_jam_kerja_text'] ?? '0 Jam' }}</td>
                <td>{{ $r['total_jam_lembur_text'] ?? '0 Jam' }}</td>
                <td>{{ $r['kehadiran_pct'] ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
