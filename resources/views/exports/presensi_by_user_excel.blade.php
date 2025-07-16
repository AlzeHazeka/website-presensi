<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Status</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Total Jam</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
        <tr>
            <td>{{ $row['tanggal'] }}</td>
            <td>{{ $row['hari'] }}</td>
            <td>{{ $row['status'] }}</td>
            <td>{{ $row['jam_masuk'] }}</td>
            <td>{{ $row['jam_keluar'] }}</td>
            <td>{{ $row['total_jam'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
