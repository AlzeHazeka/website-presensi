@php
    \Carbon\Carbon::setLocale('id');
    $period = \Carbon\Carbon::createFromFormat('Y-m', "{$tahun}-{$bulan}")->translatedFormat('F Y');
@endphp

<table>
    <tbody>
        <tr>
            <td><strong>Rekap Presensi Bulanan</strong></td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ $period }}</td>
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
            <th>Jumlah Presensi</th>
            <th>Jumlah Izin/Cuti</th>
            <th>Jumlah Lembur</th>
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
                <td>{{ $r['kehadiran_pct'] ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
