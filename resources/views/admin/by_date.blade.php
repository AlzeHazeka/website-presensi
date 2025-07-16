<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Presensi Berdasarkan Tanggal
      </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
      {{-- Form Pilih Tanggal --}}
      <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
          <label for="tanggal" class="text-lg font-semibold text-gray-700 mb-2 sm:mb-0">
            Pilih Tanggal:
          </label>
          <input
            type="date"
            id="tanggal"
            name="tanggal"
            value="{{ $tanggal }}"
            onchange="this.form.submit()"
            class="border border-gray-300 rounded-md p-2 w-full sm:w-auto"
          >
        </form>
      </div>

      {{-- Tabel Presensi --}}
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full w-full border-collapse">
            <thead>
              <tr class="bg-gray-200 text-gray-700">
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Nama</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Jam Masuk</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Lokasi Masuk</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Jam Keluar</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Lokasi Keluar</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Total Jam</th>
                <th class="border text-center py-2 px-2 sm:py-3 sm:px-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($presensi as $data)
                <tr class="border hover:bg-gray-100 transition">
                  {{-- Nama --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    {{ $data->user?->nama ?? '-' }}
                  </td>

                  {{-- Jam Masuk --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    {{ $data->jam_masuk
                        ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i')
                        : '–' }}
                  </td>

                  {{-- Lokasi Masuk --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    @if ($data->lokasi_masuk)
                      <a href="https://maps.google.com?q={{ $data->lokasi_masuk }}"
                         target="_blank"
                         class="text-blue-500 underline text-sm sm:text-base">
                        Lihat
                      </a>
                    @else
                      <span class="text-gray-500 text-sm">–</span>
                    @endif
                  </td>

                  {{-- Jam Keluar --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    {{ $data->jam_keluar
                        ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i')
                        : '–' }}
                  </td>

                  {{-- Lokasi Keluar --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    @if ($data->lokasi_keluar)
                      <a href="https://maps.google.com?q={{ $data->lokasi_keluar }}"
                         target="_blank"
                         class="text-blue-500 underline text-sm sm:text-base">
                        Lihat
                      </a>
                    @else
                      <span class="text-gray-500 text-sm">–</span>
                    @endif
                  </td>

                  {{-- Total Jam --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    @php
                      $diff = $data->jam_masuk && $data->jam_keluar
                        ? \Carbon\Carbon::parse($data->jam_masuk)
                            ->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar))
                        : 0;
                      $h = floor($diff/60);
                      $m = $diff%60;
                    @endphp
                    {{ $diff > 0 ? "{$h} Jam {$m} Menit" : '–' }}
                  </td>

                  {{-- Aksi --}}
                  <td class="border text-center py-2 px-2 sm:py-3 sm:px-4">
                    <div class="flex flex-col sm:flex-row sm:justify-center sm:space-x-2 space-y-2 sm:space-y-0">
                      <a href="{{ route('admin.presensi.edit', $data->id_presensi) }}"
                         class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700 transition text-sm w-full sm:w-auto">
                        Edit
                      </a>
                      <form action="{{ route('admin.presensi.destroy', $data->id_presensi) }}"
                            method="POST"
                            onsubmit="return confirm('Hapus presensi {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }} oleh {{ $data->user?->nama ?? '-' }}?');">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-700 transition text-sm w-full sm:w-auto">
                          Delete
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-3 px-4 text-center text-gray-500">
                    Data presensi tidak tersedia.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Total & Ekspor --}}
      <div class="mt-4 text-center font-semibold text-gray-700">
        Total data presensi = {{ $presensi->count() }}
      </div>

      <div class="flex flex-col sm:flex-row justify-end mb-4 space-y-2 sm:space-y-0 sm:space-x-2">
        <a href="{{ route('admin.presensi.bydate.export', ['tanggal' => $tanggal]) }}"
           class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-700 transition text-center w-full sm:w-auto">
          Download Excel
        </a>
      </div>
    </div>
  </x-app-layout>
