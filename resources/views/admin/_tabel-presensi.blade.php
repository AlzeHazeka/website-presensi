<div class="mt-6 overflow-x-auto">
  <table class="min-w-full w-full border-collapse">
    <thead>
      <tr class="bg-gray-200 text-gray-700">
        <th class="border text-center py-2 px-2">Tanggal</th>
        <th class="border text-center py-2 px-2">Hari</th>
        <th class="border text-center py-2 px-2">Status</th>
        <th class="border text-center py-2 px-2">Jam Masuk</th>
        <th class="border text-center py-2 px-2">Jam Keluar</th>
        <th class="border text-center py-2 px-2">Total Jam</th>
        <th class="border text-center py-2 px-2">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($presensi as $data)
        <tr class="border hover:bg-gray-100 transition">
          <td class="border text-center py-2 px-2">
            {{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}
          </td>
          <td class="border text-center py-2 px-2">
            {{ $data->hari }}
          </td>

          {{-- Kolom Status --}}
          <td class="border text-center py-2 px-2">
            @if($data->id_presensi)
              <span class="text-green-600 font-semibold">Presensi</span>
            @elseif($data->id_izin)
              <span class="text-yellow-600 font-semibold">Izin {{ $data->izin }}</span>
            @else
              <span class="text-gray-400 italic">Tidak Ada Data</span>
            @endif
          </td>

          {{-- Kolom Jam Masuk & Keluar --}}
          <td class="border text-center py-2 px-2">
            {{ $data->jam_masuk ? \Carbon\Carbon::parse($data->jam_masuk)->format('H:i') : '-' }}
          </td>
          <td class="border text-center py-2 px-2">
            {{ $data->jam_keluar ? \Carbon\Carbon::parse($data->jam_keluar)->format('H:i') : '-' }}
          </td>

          {{-- Kolom Total Jam --}}
          <td class="border text-center py-2 px-2">
            @php
              $diff = ($data->jam_masuk && $data->jam_keluar)
                ? \Carbon\Carbon::parse($data->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($data->jam_keluar))
                : 0;
              $h = floor($diff/60);
              $m = $diff%60;
            @endphp
            {{ $diff > 0 ? "{$h} Jam {$m} Menit" : '-' }}
          </td>

          {{-- Kolom Aksi --}}
          <td class="border text-center py-2 px-2">
            @if($data->id_presensi)
              <div class="flex justify-center space-x-2">
                <a href="{{ route('admin.presensi.edit',$data->id_presensi) }}"
                  class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700 text-sm">
                  Edit
                </a>
                <form action="{{ route('admin.presensi.destroy',$data->id_presensi) }}" method="POST"
                      onsubmit="return confirm('Hapus presensi tanggal {{ $data->tanggal }}?');">
                  @csrf @method('DELETE')
                  <button type="submit"
                    class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-700 text-sm">
                    Delete
                  </button>
                </form>
              </div>
            @elseif($data->id_izin)
              <form action="{{ route('admin.izin.destroy',$data->id_izin) }}" method="POST"
                      onsubmit="return confirm('Hapus izin tanggal {{ $data->tanggal }}?');">
                  @csrf @method('DELETE')
                  <button type="submit"
                    class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-700 text-sm">
                    Delete
                  </button>
                </form>
            @else
              <span class="text-gray-400">-</span>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
