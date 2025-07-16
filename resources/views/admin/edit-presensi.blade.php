<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Presensi
      </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">

          <form
            id="formPresensi"
            method="POST"
            action="{{ route('admin.presensi.update', $presensi->id_presensi) }}"
            class="space-y-6"
          >
            @csrf
            <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

            {{-- Nama & Tanggal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-semibold mb-1">Nama Karyawan</label>
                <p class="text-gray-700">{{ $presensi->user->nama }}</p>
              </div>
              <div>
                <label class="block font-semibold mb-1">Tanggal</label>
                <p class="text-gray-700">{{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d F Y') }}</p>
              </div>
            </div>

            {{-- Jam Masuk --}}
            <div>
              <label for="jam_masuk" class="block font-semibold mb-1">Jam Masuk</label>
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  name="jam_masuk"
                  id="jam_masuk"
                  class="flex-1 border rounded-md p-2"
                  value="{{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }}"
                >
                <button
                  type="button"
                  id="clearJamMasuk"
                  class="bg-red-500 hover:bg-red-700 text-white font-semibold rounded-md px-3 py-2 transition"
                  title="Clear Jam Masuk"
                >
                  Clear
                </button>
              </div>
            </div>

            {{-- Lokasi Masuk --}}
            <div>
              <label for="lokasi_masuk" class="block font-semibold mb-1">Lokasi Masuk</label>
              <div class="flex flex-col sm:flex-row gap-2">
                <input
                  type="text"
                  name="lokasi_masuk"
                  id="lokasi_masuk"
                  class="flex-1 border rounded-md p-2"
                  value="{{ old('lokasi_masuk', $presensi->lokasi_masuk) }}"
                >
                <button
                  type="button"
                  id="setLokasiMasuk"
                  class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md px-4 py-2 transition"
                >
                  Gunakan Lokasi Kantor
                </button>
              </div>
            </div>

            {{-- Jam Keluar --}}
            <div>
              <label for="jam_keluar" class="block font-semibold mb-1">Jam Keluar</label>
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  name="jam_keluar"
                  id="jam_keluar"
                  class="flex-1 border rounded-md p-2"
                  value="{{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') }}"
                >
                <button
                  type="button"
                  id="clearJamKeluar"
                  class="bg-red-500 hover:bg-red-700 text-white font-semibold rounded-md px-3 py-2 transition"
                  title="Clear Jam Keluar"
                >
                  Clear
                </button>
              </div>
            </div>

            {{-- Lokasi Keluar --}}
            <div>
              <label for="lokasi_keluar" class="block font-semibold mb-1">Lokasi Keluar</label>
              <div class="flex flex-col sm:flex-row gap-2">
                <input
                  type="text"
                  name="lokasi_keluar"
                  id="lokasi_keluar"
                  class="flex-1 border rounded-md p-2"
                  value="{{ old('lokasi_keluar', $presensi->lokasi_keluar) }}"
                >
                <button
                  type="button"
                  id="setLokasiKeluar"
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
        flatpickr("#jam_masuk", {
          enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true
        });
        flatpickr("#jam_keluar", {
          enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true
        });

        // Tombol Clear untuk jam masuk
        document.getElementById("clearJamMasuk").addEventListener("click", function() {
          document.getElementById("jam_masuk")._flatpickr.clear();
        });

        // Tombol Clear untuk jam keluar
        document.getElementById("clearJamKeluar").addEventListener("click", function() {
          document.getElementById("jam_keluar")._flatpickr.clear();
        });

        document.getElementById("btnSubmit").addEventListener("click", function() {
          Swal.fire({
            title: 'Yakin ingin menyimpan?',
            text: "Periksa kembali data Anda!",
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
                document.getElementById("formPresensi").submit();
              });
            }
          });
        });

        // Tombol lokasi kantor
        document.getElementById("setLokasiMasuk")
                .addEventListener("click", () => {
          document.getElementById("lokasi_masuk").value = "-7.161892,112.621675";
        });
        document.getElementById("setLokasiKeluar")
                .addEventListener("click", () => {
          document.getElementById("lokasi_keluar").value = "-7.161892,112.621675";
        });

        // Menampilkan toast jika ada session success
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
