<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Role</th>
            <th>Status</th>
            <th>Tipe Gaji</th>
            <th>Gaji / Hari</th>
            <th>Jumlah Presensi</th>
            <th>Total Gaji</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekap as $r)
            <tr>
                <td>{{ $r['nama'] }}</td>
                <td>{{ $r['role'] }}</td>
                <td>{{ $r['status'] }}</td>
                <td>{{ $r['tipe_gaji'] }}</td>
                <td>{{ $r['gaji'] }}</td>
                <td>{{ $r['jumlah_presensi'] }}</td>
                <td>{{ $r['total_gaji'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
