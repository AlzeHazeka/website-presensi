<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rekap Presensi Bulanan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-4 shadow rounded mb-4">
                <form method="GET" action="{{ route('admin.presensi.rekap.presensi') }}" class="flex items-center gap-4">
                    <div>
                        <label>Bulan:</label>
                        <select name="bulan" class="border rounded p-2">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label>Tahun:</label>
                        <input type="number" name="tahun" value="{{ $tahun }}" class="border rounded p-2">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Tampilkan</button>
                    <div>
                        <a href="{{ route('admin.presensi.rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="px-4 py-2 bg-green-600 text-white rounded">
                            Download Excel
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 shadow rounded">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Tipe Gaji</th>
                            <th class="px-4 py-2">Gaji / Hari</th>
                            <th class="px-4 py-2">Jumlah Presensi</th>
                            <th class="px-4 py-2">Total Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap as $r)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $r['nama'] }}</td>
                                <td class="px-4 py-2">{{ $r['role'] }}</td>
                                <td class="px-4 py-2">{{ $r['status'] }}</td>
                                <td class="px-4 py-2 capitalize">{{ $r['tipe_gaji'] }}</td>
                                <td class="px-4 py-2">Rp {{ number_format($r['gaji'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $r['jumlah_presensi'] }}</td>
                                <td class="px-4 py-2 font-bold">Rp {{ number_format($r['total_gaji'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
