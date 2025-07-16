<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Deskripsi</th>
            <th>Total Jam</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lembur as $item)
            @php
                $diff = \Carbon\Carbon::parse($item->jam_mulai_lembur)->diffInMinutes(\Carbon\Carbon::parse($item->jam_pulang_lembur));
                $h = floor($diff / 60);
                $m = $diff % 60;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l') }}</td>
                <td>{{ $item->jam_mulai_lembur ? \Carbon\Carbon::parse($item->jam_mulai_lembur)->format('H:i') : '-' }}</td>
                <td>{{ $item->jam_pulang_lembur ? \Carbon\Carbon::parse($item->jam_pulang_lembur)->format('H:i') : '-' }}</td>
                <td>{{ $item->deskripsi ?? '-' }}</td>
                <td>{{ "{$h} Jam {$m} Menit" }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
