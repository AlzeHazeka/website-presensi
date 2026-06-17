<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji Karyawan Harian</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #111827;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.45;
        }

        .print-page {
            width: 100%;
        }

        .salary-slip {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 24px;
        }

        .company {
            color: #4b5563;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        h1 {
            margin: 8px 0 4px;
            font-size: 22px;
            letter-spacing: 0.08em;
            text-align: center;
        }

        .period {
            text-align: center;
            color: #4b5563;
            margin-bottom: 20px;
        }

        .total-box {
            margin: 18px 0 22px;
            padding: 18px;
            border: 1px solid #111827;
            border-radius: 8px;
            text-align: center;
        }

        .total-label {
            font-size: 12px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .total-value {
            margin-top: 4px;
            font-size: 30px;
            font-weight: 700;
        }

        .section {
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            margin-top: 16px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 4px 0;
        }

        .label {
            color: #4b5563;
        }

        .value {
            font-weight: 700;
            text-align: right;
        }

        .note {
            margin-top: 18px;
            color: #4b5563;
            font-size: 12px;
        }

        .meta {
            margin-top: 12px;
            color: #6b7280;
            font-size: 11px;
            text-align: right;
        }

        .attachment {
            page-break-before: always;
            padding-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        @media print {
            .no-print {
                display: none;
            }

            .salary-slip {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    $rupiah = static fn ($value) => 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
@endphp

<main class="print-page">
    <section class="salary-slip">
        <div class="company">IPS Internal System / CV Irfan Putera Sejahtera</div>
        <h1>SLIP GAJI KARYAWAN HARIAN</h1>
        <div class="period">{{ $period['label'] ?? '-' }}</div>

        <div class="total-box">
            <div class="total-label">Total Gaji</div>
            <div class="total-value">{{ $rupiah($payroll['gross_total'] ?? 0) }}</div>
        </div>

        <section class="section">
            <div class="section-title">Identitas Karyawan</div>
            <div class="row">
                <div class="label">Nama Karyawan</div>
                <div class="value">{{ $employee['nama'] ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="label">NIK</div>
                <div class="value">{{ $employee['nik'] ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="label">Posisi</div>
                <div class="value">{{ $employee['posisi'] ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="label">Tipe Gaji</div>
                <div class="value">Harian</div>
            </div>
            <div class="row">
                <div class="label">Periode</div>
                <div class="value">{{ $period['label'] ?? '-' }}</div>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Rincian Presensi</div>
            <div class="row">
                <div class="label">Total Hari Hadir</div>
                <div class="value">{{ $summary['total_valid_attendance_days'] ?? 0 }} hari</div>
            </div>
            <div class="row">
                <div class="label">Total Jam Aktual</div>
                <div class="value">{{ $summary['total_work_hours_label'] ?? '0 jam 0 menit' }}</div>
            </div>
            <div class="row">
                <div class="label">Total Jam Dibayar</div>
                <div class="value">{{ $summary['rounded_payable_hours'] ?? 0 }} jam</div>
            </div>
            <div class="row">
                <div class="label">Standar Jam/Hari</div>
                <div class="value">{{ $payroll['standard_hours_per_day'] ?? 8 }} jam</div>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Rincian Izin / Cuti</div>
            <div class="row">
                <div class="label">Total Izin/Cuti Disetujui</div>
                <div class="value">{{ $leaveSummary['total_items'] ?? 0 }} pengajuan</div>
            </div>
            <div class="row">
                <div class="label">Total Hari Izin/Cuti</div>
                <div class="value">{{ $leaveSummary['total_days'] ?? 0 }} hari</div>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Rincian Lembur</div>
            <div class="row">
                <div class="label">Total Lembur Disetujui</div>
                <div class="value">{{ $overtimeSummary['total_items'] ?? 0 }} data</div>
            </div>
            <div class="row">
                <div class="label">Total Jam Lembur Aktual</div>
                <div class="value">{{ $overtimeSummary['total_overtime_hours_label'] ?? '0 jam 0 menit' }}</div>
            </div>
            <div class="row">
                <div class="label">Total Jam Lembur Bayar</div>
                <div class="value">{{ $overtimeSummary['payable_overtime_hours'] ?? 0 }} jam</div>
            </div>
            <div class="row">
                <div class="label">Konversi Lembur</div>
                <div class="value">{{ $overtimeSummary['overtime_conversion_label'] ?? $payroll['overtime_conversion_label'] ?? '0 hari kerja + 0 jam' }}</div>
            </div>
            <div class="row">
                <div class="label">Pendapatan Lembur</div>
                <div class="value">{{ $rupiah($payroll['overtime_total'] ?? 0) }}</div>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Rincian Gaji</div>
            <div class="row">
                <div class="label">Gaji Per Hari</div>
                <div class="value">{{ $rupiah($payroll['daily_wage'] ?? 0) }}</div>
            </div>
            <div class="row">
                <div class="label">Gaji Per Jam</div>
                <div class="value">{{ $rupiah($payroll['hourly_wage'] ?? 0) }}</div>
            </div>
            <div class="row">
                <div class="label">Jam Kerja Dibayar</div>
                <div class="value">{{ $payroll['payable_hours'] ?? 0 }} jam</div>
            </div>
            <div class="row">
                <div class="label">Gaji Utama</div>
                <div class="value">{{ $rupiah($payroll['attendance_gross_total'] ?? 0) }}</div>
            </div>
            <div class="row">
                <div class="label">Pendapatan Lembur</div>
                <div class="value">{{ $rupiah($payroll['overtime_total'] ?? 0) }}</div>
            </div>
        </section>

        <div class="total-box">
            <div class="total-label">Total Gaji</div>
            <div class="total-value">{{ $rupiah($payroll['gross_total'] ?? 0) }}</div>
        </div>

        <div class="note">Catatan: Perhitungan berdasarkan presensi dan lembur lengkap serta valid. Izin/cuti ditampilkan sebagai informasi.</div>
        <div class="meta">Dicetak: {{ $generatedAt ?? '-' }}</div>
    </section>

    <section class="attachment">
        <h2>Lampiran Detail Presensi</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Durasi Kerja</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance['tanggal_human'] ?? $attendance['tanggal'] ?? '-' }}</td>
                        <td>{{ $attendance['jam_masuk'] ?? '-' }}</td>
                        <td>{{ $attendance['jam_keluar'] ?? '-' }}</td>
                        <td>{{ $attendance['duration_label'] ?? '-' }}</td>
                        <td>{{ $attendance['status'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada data presensi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Lampiran Detail Izin/Cuti</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Alasan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($leaveSummary['items'] ?? []) as $leave)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $leave['date_label'] ?? $leave['date'] ?? '-' }}</td>
                        <td>{{ $leave['type'] ?? '-' }}</td>
                        <td>{{ $leave['reason'] ?? '-' }}</td>
                        <td>{{ $leave['status'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada izin/cuti disetujui pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Lampiran Detail Lembur</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Durasi Lembur</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($overtimeSummary['items'] ?? []) as $overtime)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $overtime['tanggal_human'] ?? $overtime['tanggal'] ?? '-' }}</td>
                        <td>{{ $overtime['jam_mulai'] ?? '-' }}</td>
                        <td>{{ $overtime['jam_selesai'] ?? '-' }}</td>
                        <td>{{ $overtime['duration_label'] ?? '-' }}</td>
                        <td>{{ $overtime['status'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada lembur disetujui pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</main>

<script>
    window.addEventListener('load', () => window.print());
</script>
</body>
</html>
