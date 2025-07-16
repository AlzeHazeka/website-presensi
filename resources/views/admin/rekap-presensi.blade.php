<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Rekap Presensi Bulanan
      </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-6">

        {{-- Filter & Export --}}
        <div class="bg-white shadow rounded-lg p-4">
          <form
            method="GET"
            action="{{ route('admin.presensi.rekap.presensi') }}"
            class="flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0"
          >
            {{-- Bulan --}}
            <div class="flex-1">
              <label class="block font-semibold mb-1">Bulan</label>
              <select
                name="bulan"
                class="w-full border rounded-md p-2"
              >
                @for ($i = 1; $i <= 12; $i++)
                  <option
                    value="{{ str_pad($i,2,'0',STR_PAD_LEFT) }}"
                    @selected($bulan==str_pad($i,2,'0',STR_PAD_LEFT))
                  >
                    {{ \Carbon\Carbon::create(null,$i)->translatedFormat('F') }}
                  </option>
                @endfor
              </select>
            </div>

            {{-- Tahun --}}
            <div class="flex-1">
              <label class="block font-semibold mb-1">Tahun</label>
              <input
                type="number"
                name="tahun"
                value="{{ $tahun }}"
                class="w-full border rounded-md p-2"
              >
            </div>

            {{-- Tombol Tampilkan --}}
            <div class="w-full sm:w-auto">
              <button
                type="submit"
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md px-4 py-2 transition"
              >
                Tampilkan
              </button>
            </div>

            {{-- Tombol Export --}}
            <div class="w-full sm:w-auto">
              <a
                href="{{ route('admin.presensi.rekap.export',['bulan'=>$bulan,'tahun'=>$tahun]) }}"
                class="w-full sm:w-auto block bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md px-4 py-2 text-center transition"
              >
                Download Excel
              </a>
            </div>
          </form>
        </div>

        {{-- Tabel Rekap --}}
        <div class="bg-white shadow rounded-lg p-4 overflow-x-auto">
        <table class="min-w-full w-full table-auto border-collapse">
            <thead class="bg-gray-100">
            <tr>
                <th class="px-3 py-2 text-left text-sm font-medium">Nama</th>
                <th class="px-3 py-2 text-left text-sm font-medium">Posisi</th>
                <th class="px-3 py-2 text-left text-sm font-medium">Status</th>
                <th class="px-3 py-2 text-left text-sm font-medium">Tipe Gaji</th>
                <th class="px-3 py-2 text-right text-sm font-medium">Gaji</th>
                <th class="px-3 py-2 text-center text-sm font-medium">Presensi</th>
                <th class="px-3 py-2 text-center text-sm font-medium">Lembur</th>
                <th class="px-3 py-2 text-center text-sm font-medium">Izin</th>
                <!--<th class="px-3 py-2 text-right text-sm font-medium">Total Gaji</th>-->
            </tr>
            </thead>
            <tbody>
            @foreach($rekap as $r)
                <tr class="border-t hover:bg-gray-50 transition">
                <td class="px-3 py-2 text-sm">{{ $r['nama'] }}</td>
                <td class="px-3 py-2 text-sm">{{ $r['posisi'] }}</td>
                <td class="px-3 py-2 text-sm">{{ $r['status'] }}</td>
                <td class="px-3 py-2 text-sm capitalize">{{ $r['tipe_gaji'] }}</td>
                <td class="px-3 py-2 text-sm text-right">
                    Rp {{ number_format($r['gaji'],0,',','.') }}
                </td>
                <td class="px-3 py-2 text-sm text-center">{{ $r['jumlah_presensi'] }}</td>
                <td class="px-3 py-2 text-sm text-center">{{ $r['jumlah_lembur'] }}</td>
                <td class="px-3 py-2 text-sm text-center">{{ $r['jumlah_izin'] }}</td>
                <!--<td class="px-3 py-2 text-sm font-semibold text-right">
                    Rp {{ number_format($r['total_gaji'],0,',','.') }}
                </td>-->
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>


      </div>
    </div>
  </x-app-layout>
