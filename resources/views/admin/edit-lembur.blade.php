<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit Data Lembur
    </h2>
  </x-slot>

  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
      <div class="bg-white shadow-xl sm:rounded-lg p-6">

        <form
          id="formLembur"
          method="POST"
          action="{{ route('admin.lembur.update', $lembur->id_lembur) }}"
          class="space-y-6"
        >
          @csrf
          <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

          {{-- Nama & Tanggal --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1">Nama Karyawan</label>
              <p class="text-gray-700">{{ $lembur->user->nama }}</p>
            </div>
            <div>
              <label class="block font-semibold mb-1">Tanggal</label>
              <p class="text-gray-700">{{ \Carbon\Carbon::parse($lembur->tanggal)->translatedFormat('d F Y') }}</p>
            </div>
          </div>

          {{-- Jam Mulai Lembur --}}
          <div>
            <label for="jam_mulai_lembur" class="block font-semibold mb-1">Jam Mulai Lembur</label>
            <div class="flex items-center gap-2">
              <input
                type="text"
                name="jam_mulai_lembur"
                id="jam_mulai_lembur"
                class="flex-1 border rounded-md p-2"
                value="{{ \Carbon\Carbon::parse($lembur->jam_mulai_lembur)->format('H:i') }}"
              >
              <button
                type="button"
                id="clearJamMulai"
                class="bg-red-500 hover:bg-red-700 text-white font-semibold rounded-md px-3 py-2 transition"
                title="Clear Jam Mulai"
              >
                Clear
              </button>
            </div>
          </div>

          {{-- Lokasi Mulai Lembur --}}
          <div>
            <label for="lokasi_mulai_lembur" class="block font-semibold mb-1">Lokasi Mulai Lembur</label>
            <div class="flex flex-col sm:flex-row gap-2">
              <input
                type="text"
                name="lokasi_mulai_lembur"
                id="lokasi_mulai_lembur"
                class="flex-1 border rounded-md p-2"
                value="{{ old('lokasi_mulai_lembur', $lembur->lokasi_mulai_lembur) }}"
              >
              <button
                type="button"
                id="setLokasiMulai"
                class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md px-4 py-2 transition"
              >
                Gunakan Lokasi Kantor
              </button>
            </div>
          </div>

          {{-- Jam Pulang Lembur --}}
          <div>
            <label for="jam_pulang_lembur" class="block font-semibold mb-1">Jam Pulang Lembur</label>
            <div class="flex items-center gap-2">
              <input
                type="text"
                name="jam_pulang_lembur"
                id="jam_pulang_lembur"
                class="flex-1 border rounded-md p-2"
                value="{{ \Carbon\Carbon::parse($lembur->jam_pulang_lembur)->format('H:i') }}"
              >
              <button
                type="button"
                id="clearJamPulang"
                class="bg-red-500 hover:bg-red-700 text-white font-semibold rounded-md px-3 py-2 transition"
                title="Clear Jam Pulang"
              >
                Clear
              </button>
            </div>
          </div>

          {{-- Lokasi Pulang Lembur --}}
          <div>
            <label for="lokasi_pulang_lembur" class="block font-semibold mb-1">Lokasi Pulang Lembur</label>
            <div class="flex flex-col sm:flex-row gap-2">
              <input
                type="text"
                name="lokasi_pulang_lembur"
                id="lokasi_pulang_lembur"
                class="flex-1 border rounded-md p-2"
                value="{{ old('lokasi_pulang_lembur', $lembur->lokasi_pulang_lembur) }}"
              >
              <button
                type="button"
                id="setLokasiPulang"
                class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md px-4 py-2 transition"
              >
                Gunakan Lokasi Kantor
              </button>
            </div>
          </div>

          {{-- Aksi --}}
          <div class="flex flex-col sm:flex-row sm:justify-end gap-4">
            <button
              type="button"
              id="btnSubmit"
              class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-semibold rounded-md px-4 py-2 transition"
            >
              Simpan Perubahan
            </button>
            <a
              href="{{ url()->previous() }}"
              class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-md px-4 py-2 text-center transition"
            >
              Batal
            </a>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- Flatpickr & SweetAlert --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      flatpickr("#jam_mulai_lembur", {
        enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true
      });
      flatpickr("#jam_pulang_lembur", {
        enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true
      });

      document.getElementById("clearJamMulai").addEventListener("click", function() {
        document.getElementById("jam_mulai_lembur")._flatpickr.clear();
      });
      document.getElementById("clearJamPulang").addEventListener("click", function() {
        document.getElementById("jam_pulang_lembur")._flatpickr.clear();
      });

      document.getElementById("btnSubmit").addEventListener("click", function() {
        Swal.fire({
          title: 'Yakin ingin menyimpan?',
          text: "Periksa kembali data lembur!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, simpan',
          cancelButtonText: 'Batal'
        }).then((res) => {
          if (res.isConfirmed) {
            Swal.fire({
              icon: 'success',
              title: 'Tersimpan!',
              showConfirmButton: false,
              timer: 1200
            }).then(() => {
              document.getElementById("formLembur").submit();
            });
          }
        });
      });

      document.getElementById("setLokasiMulai").addEventListener("click", function() {
        document.getElementById("lokasi_mulai_lembur").value = "-7.161892,112.621675";
      });
      document.getElementById("setLokasiPulang").addEventListener("click", function() {
        document.getElementById("lokasi_pulang_lembur").value = "-7.161892,112.621675";
      });

      @if(session('success'))
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: '{{ session("success") }}',
          timer: 1800,
          showConfirmButton: false
        });
      @endif
    });
  </script>
</x-app-layout>
