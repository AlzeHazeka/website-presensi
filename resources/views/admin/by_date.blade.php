<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Presensi Berdasarkan Tanggal
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto">
        <!-- Form Pilih Tanggal -->
        <div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <form method="GET">
                <label for="tanggal" class="block text-lg font-semibold text-gray-700 mb-2">
                    Pilih Tanggal:
                </label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-md p-2 w-full sm:w-auto">
            </form>
        </div>

        <!-- Tabel Presensi -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full border-collapse w-full">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-3 px-4 border text-center">Nama</th>
                        <th class="py-3 px-4 border text-center">Jam Masuk</th>
                        <th class="py-3 px-4 border text-center">Lokasi Masuk</th>
                        <th class="py-3 px-4 border text-center">Jam Keluar</th>
                        <th class="py-3 px-4 border text-center">Lokasi Keluar</th>
                        <th class="py-3 px-4 border text-center">Total Jam</th>
                        <th class="py-3 px-4 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($presensi as $data)
                        <tr class="border hover:bg-gray-100 transition">
                            <td class="py-3 px-4 border text-center">{{ $data->user->nama }}</td>
                            <td class="py-3 px-4 border text-center">
                                {{ $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : 'Belum Presensi' }}
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
                                {{ $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : 'Belum Presensi' }}
                            </td>
                            <td class="py-3 px-4 border text-center">
                                @if ($data->lokasi_keluar)
                                    <a href="https://www.google.com/maps?q={{ $data->lokasi_keluar }}" target="_blank" class="text-blue-500 underline">
                                        Lihat di Maps
                                    </a>
                                @else
                                    <span class="text-gray-500">Tidak Tersedia</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border text-center">
                                @php
                                    $diffInMinutes = $data->jam_masuk && $data->jam_keluar
                                        ? \Carbon\Carbon::parse($data->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar))
                                        : 0;
                                    $hours = floor($diffInMinutes / 60);
                                    $minutes = $diffInMinutes % 60;
                                @endphp
                                {{ $diffInMinutes > 0 ? "$hours Jam $minutes Menit" : '-' }}
                            </td>
                            <td class="py-3 px-4 border text-center">
                                <a href="{{ route('admin.presensi.edit', $data->id_presensi) }}"
                                    class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-3 px-4 text-center text-gray-500">
                                Data presensi tidak tersedia untuk tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Total Presensi -->
        <div class="mt-4 text-center font-semibold text-gray-700">
            Total data presensi = {{ $presensi->count() }}
        </div>

        <div class="flex justify-end mb-4">
            <!-- Tombol Cetak PDF -->
            <a href="{{ route('admin.presensi.export.pdf', ['tanggal' => $tanggal]) }}"
                class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-700 transition mr-2">
                Cetak PDF
            </a>

            <!-- Tombol Cetak Excel -->
            <a href="{{ route('admin.presensi.export.excel', ['tanggal' => $tanggal]) }}"
                class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-700 transition">
                Download Excel
            </a>
        </div>

    </div>
</x-app-layout>
