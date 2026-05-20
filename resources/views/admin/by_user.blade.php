<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Presensi Berdasarkan Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">

                <!-- Form Pencarian -->
                <form method="GET">
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Pilih Karyawan -->
                        <div>
                            <label for="user_id" class="block font-semibold">Pilih Karyawan:</label>
                            <select name="user_id" id="user_id" class="w-full border rounded p-2" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->user_id }}" @selected($user->user_id == request('user_id'))>
                                        {{ $user->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Bulan -->
                        <div>
                            <label for="bulan" class="block font-semibold">Pilih Bulan:</label>
                            <select name="bulan" id="bulan" class="w-full border rounded p-2">
                                @foreach (range(1, 12) as $i)
                                    @php
                                        $namaBulan = \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F');
                                    @endphp
                                    <option value="{{ $i }}" @selected($i == request('bulan'))>
                                        {{ $namaBulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Tahun -->
                        <div>
                            <label for="tahun" class="block font-semibold">Pilih Tahun:</label>
                            <select name="tahun" id="tahun" class="w-full border rounded p-2">
                                @for ($i = now()->year; $i >= now()->year - 5; $i--)
                                    <option value="{{ $i }}" @selected($i == request('tahun'))>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Cari</button>
                    </div>
                </form>

                <!-- Tabel Presensi -->
                @if (count($presensi) > 0)
                    <table class="min-w-full bg-white mt-6 border">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border px-4 py-2">Tanggal</th>
                                <th class="border px-4 py-2">Hari</th>
                                <th class="border px-4 py-2">Jam Masuk</th>
                                <th class="border px-4 py-2">Lokasi Masuk</th>
                                <th class="border px-4 py-2">Jam Keluar</th>
                                <th class="border px-4 py-2">Lokasi keluar</th>
                                <th class="border px-4 py-2">Total Jam</th>
                                <th class="border px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($presensi as $data)
                                <tr>
                                    <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}</td>
                                    <td class="border px-4 py-2">{{ $data->hari }}</td>
                                    <td class="py-3 px-4 border text-center">
                                        {{ $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : 'Belum Presensi Masuk' }}
                                    </td>
                                    <td class="py-3 px-4 border text-center">
                                        @if ( $data->lokasi_masuk)
                                            <a href="https://www.google.com/maps?q={{ $data->lokasi_masuk }}" target="_blank" class="text-blue-500 underline">
                                                Lihat di Maps
                                            </a>
                                        @else
                                            <span class="text-gray-500">Tidak Tersedia</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border text-center">
                                        {{ $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : 'Belum Presensi Keluar' }}
                                    </td>
                                    <td class="py-3 px-4 border text-center">
                                        @if ($data->lokasi_masuk)
                                            <a href="https://www.google.com/maps?q={{ $data->lokasi_masuk }}" target="_blank" class="text-blue-500 underline">
                                                Lihat di Maps
                                            </a>
                                        @else
                                            <span class="text-gray-500">Tidak Tersedia</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border text-center">
                                        @php
                                            $diffInMinutes = \Carbon\Carbon::parse($data->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar));
                                            $hours = floor($diffInMinutes / 60); // Ambil jam
                                            $minutes = $diffInMinutes % 60; // Sisa menit
                                        @endphp
                                        {{ $hours }} Jam {{ $minutes }} Menit
                                    </td>
                                    <td class="border px-4 py-2">
                                        @if ($data->id_presensi)
                                            <a href="{{ route('admin.presensi.edit', ['id' => $data->id_presensi]) }}"
                                            class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition">Edit</a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Menampilkan Total Hari dan Presensi -->
                    <div class="mt-4 text-center">
                        <div class="flex justify-between items-center">
                            <p class="">Total Hari dalam Bulan:</p>
                            <p class="">{{ $jumlahHari }} Hari</p>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <p class="">Total Presensi yang Tercatat:</p>
                            <p class="">
                                {{ collect($presensi)->filter(function ($item) {
                                    return $item->jam_masuk && $item->jam_keluar;
                                })->count() }} Hari
                            </p>
                        </div>

                        @php
                            // Ambil user yang dipilih
                            $selectedUser = $users->firstWhere('user_id', request('user_id'));

                            // Hitung total presensi valid (jam masuk dan keluar terisi)
                            $totalPresensiValid = collect($presensi)->filter(function ($item) {
                                return $item->jam_masuk && $item->jam_keluar;
                            })->count();

                            // Cek jika user ditemukan dan ada tipe gaji
                            $gajiText = '-';
                            if ($selectedUser) {
                                if ($selectedUser->tipe_gaji === 'harian') {
                                    $totalGaji = $selectedUser->gaji * $totalPresensiValid;
                                    $gajiText = 'Total Gaji (Harian): Rp ' . number_format($totalGaji, 0, ',', '.');
                                } elseif ($selectedUser->tipe_gaji === 'bulanan') {
                                    $gajiText = 'Gaji anda adalah bulanan: Rp ' . number_format($selectedUser->gaji, 0, ',', '.');
                                }
                            }
                        @endphp

                        <div class="flex justify-between items-center mt-4">
                            <p class="text-lg font-semibold">Perhitungan Gaji:</p>
                            <p class="text-lg text-green-600">{{ $gajiText }}</p>
                        </div>

                    </div>
                @else
                    <p class="mt-6 text-center text-gray-500">
                        Tidak ada data presensi untuk karyawan ini pada bulan
                        {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
