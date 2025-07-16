<div class="mt-6 overflow-x-auto">
  <table class="min-w-full w-full border-collapse">
    <thead>
      <tr class="bg-gray-200 text-gray-700">
        <th class="border text-center py-2 px-2">Tanggal</th>
        <th class="border text-center py-2 px-2">Hari</th>
        <th class="border text-center py-2 px-2">Jam Mulai</th>
        <th class="border text-center py-2 px-2">Jam Selesai</th>
        <th class="border text-center py-2 px-2">Deskripsi</th>
        <th class="border text-center py-2 px-2">Total Jam</th>
        <th class="border text-center py-2 px-2">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($lembur as $item)
        <tr class="border hover:bg-gray-100 transition">
          <td class="border text-center py-2 px-2">
            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
          </td>
          <td class="border text-center py-2 px-2">
            {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l') }}
          </td>
          <td class="border text-center py-2 px-2">
            {{ $item->jam_mulai_lembur ? \Carbon\Carbon::parse($item->jam_mulai_lembur)->format('H:i') : '-'}}
          </td>
          <td class="border text-center py-2 px-2">
            {{ $item->jam_pulang_lembur ? \Carbon\Carbon::parse($item->jam_pulang_lembur)->format('H:i') : '-' }}
          </td>
          <td class="border text-center py-2 px-2">
            {{ $item->deskripsi ?? '-' }}
          </td>
          <td class="border text-center py-2 px-2">
            @php
                $diff = \Carbon\Carbon::parse($item->jam_mulai_lembur)->diffInMinutes(\Carbon\Carbon::parse($item->jam_pulang_lembur));
                $h = floor($diff / 60);
                $m = $diff % 60;
            @endphp
            {{ "{$h} Jam {$m} Menit" }}
          </td>
          <td class="border text-center py-2 px-2">
            <div class="flex justify-center space-x-2">
              <a href="{{ route('admin.lembur.edit',$item->id_lembur) }}"
                class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700 text-sm">
                Edit
              </a>
              <form action="{{ route('admin.lembur.destroy', $item->id_lembur) }}"
                    method="POST"
                    onsubmit="return confirm('Hapus data lembur tanggal {{ $item->tanggal }}?');">
                @csrf @method('DELETE')
                <button type="submit"
                        class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-700 text-sm">
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-gray-500 py-4 italic">
            Tidak ada data lembur untuk bulan ini.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
