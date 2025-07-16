<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Presensi</h2>
    <p style="text-align: center;">Tanggal: {{ $tanggal }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jam Masuk</th>
                <th>Lokasi Masuk</th>
                <th>Jam Keluar</th>
                <th>Lokasi Keluar</th>
                <th>Total Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($presensi as $data)
                <tr>
                    <td>{{ $data->user->nama }}</td>
                    <td>{{ $data->jam_masuk ?? 'Belum Presensi' }}</td>
                    <td>{{ $data->lokasi_masuk ?? 'Tidak Tersedia' }}</td>
                    <td>{{ $data->jam_keluar ?? 'Belum Presensi' }}</td>
                    <td>{{ $data->lokasi_keluar ?? 'Tidak Tersedia' }}</td>
                    <td>
                        @php
                            $diffInMinutes = $data->jam_masuk && $data->jam_keluar
                                ? \Carbon\Carbon::parse($data->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar))
                                : 0;
                            $hours = floor($diffInMinutes / 60);
                            $minutes = $diffInMinutes % 60;
                        @endphp
                        {{ $diffInMinutes > 0 ? "$hours Jam $minutes Menit" : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
