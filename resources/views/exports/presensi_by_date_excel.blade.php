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
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['Nama'] }}</td>
                <td>{{ $row['Jam Masuk'] }}</td>
                <td>{{ $row['Lokasi Masuk'] }}</td>
                <td>{{ $row['Jam Keluar'] }}</td>
                <td>{{ $row['Lokasi Keluar'] }}</td>
                <td>{{ $row['Total Jam'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
