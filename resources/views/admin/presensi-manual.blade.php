<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Tambah Presensi Manual
      </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">

            <form
                id="formPresensiManual"
                method="POST"
                action="{{ route('admin.presensi.store') }}"
                class="space-y-6"
            >
                @csrf

                {{-- Pilih Tipe --}}
                <div>
                <label for="tipe" class="block font-semibold mb-1">Tipe Data</label>
                <select
                    name="tipe"
                    id="tipe"
                    required
                    class="w-full border rounded-md p-2"
                >
                    <option value="">-- Pilih Tipe --</option>
                    <option value="presensi">Presensi</option>
                    <option value="lembur">Lembur</option>
                    <option value="izin">Izin</option>
                </select>
                </div>

                {{-- Pilih Karyawan & Tanggal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="user_id" class="block font-semibold mb-1">Pilih Karyawan</label>
                    <select
                    name="user_id"
                    id="user_id"
                    required
                    class="w-full border rounded-md p-2"
                    >
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->user_id }}">{{ $user->nama }}</option>
                    @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="block font-semibold mb-1">Tanggal</label>
                    <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    required
                    class="w-full border rounded-md p-2"
                    >
                </div>
                </div>

                {{-- Jam Masuk & Keluar --}}
                <div id="jamFields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jam_masuk" class="block font-semibold mb-1" id="label_jam_masuk">Jam Masuk</label>
                    <input
                    type="time"
                    name="jam_masuk"
                    id="jam_masuk"
                    class="w-full border rounded-md p-2"
                    >
                </div>

                <div>
                    <label for="jam_keluar" class="block font-semibold mb-1" id="label_jam_keluar">Jam Keluar</label>
                    <input
                    type="time"
                    name="jam_keluar"
                    id="jam_keluar"
                    class="w-full border rounded-md p-2"
                    >
                </div>
                </div>

                {{-- Lokasi Masuk --}}
                <div id="lokasiMasukContainer">
                <label for="lokasi_masuk" class="block font-semibold mb-1" id="label_lokasi_masuk">Lokasi Masuk</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input
                    type="text"
                    name="lokasi_masuk"
                    id="lokasi_masuk"
                    class="flex-1 border rounded-md p-2"
                    >
                    <button
                    type="button"
                    id="btnLokasiMasuk"
                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md px-4 py-2 transition"
                    >
                    Gunakan Lokasi Kantor
                    </button>
                </div>
                </div>

                {{-- Lokasi Keluar --}}
                <div id="lokasiKeluarContainer">
                <label for="lokasi_keluar" class="block font-semibold mb-1" id="label_lokasi_keluar">Lokasi Keluar</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input
                    type="text"
                    name="lokasi_keluar"
                    id="lokasi_keluar"
                    class="flex-1 border rounded-md p-2"
                    >
                    <button
                    type="button"
                    id="btnLokasiKeluar"
                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md px-4 py-2 transition"
                    >
                    Gunakan Lokasi Kantor
                    </button>
                </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-col sm:flex-row justify-end gap-4">
                <button
                    type="button"
                    id="btnSubmit"
                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md px-4 py-2 transition"
                >
                    Simpan Data
                </button>
                <a
                    href="{{ route('admin.presensi.by-date') }}"
                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-md px-4 py-2 text-center transition"
                >
                    Batal
                </a>
                </div>
            </form>
            </div>
        </div>
        </div>

    {{-- Flatpickr & SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
      document.getElementById('tipe').addEventListener('change', function () {
    const tipe = this.value;

    const labelJamMasuk = document.getElementById('label_jam_masuk');
    const labelJamKeluar = document.getElementById('label_jam_keluar');
    const labelLokasiMasuk = document.getElementById('label_lokasi_masuk');
    const labelLokasiKeluar = document.getElementById('label_lokasi_keluar');

    const jamFields = document.getElementById('jamFields');
    const lokasiMasuk = document.getElementById('lokasiMasukContainer');
    const lokasiKeluar = document.getElementById('lokasiKeluarContainer');

    if (tipe === 'presensi') {
      labelJamMasuk.textContent = 'Jam Masuk';
      labelJamKeluar.textContent = 'Jam Keluar';
      labelLokasiMasuk.textContent = 'Lokasi Masuk';
      labelLokasiKeluar.textContent = 'Lokasi Keluar';

      jamFields.style.display = 'grid';
      lokasiMasuk.style.display = 'block';
      lokasiKeluar.style.display = 'block';

    } else if (tipe === 'lembur') {
      labelJamMasuk.textContent = 'Jam Mulai Lembur';
      labelJamKeluar.textContent = 'Jam Selesai Lembur';
      labelLokasiMasuk.textContent = 'Lokasi Mulai Lembur';
      labelLokasiKeluar.textContent = 'Lokasi Selesai Lembur';

      jamFields.style.display = 'grid';
      lokasiMasuk.style.display = 'block';
      lokasiKeluar.style.display = 'block';

    } else if (tipe === 'izin') {
      jamFields.style.display = 'none';
      lokasiMasuk.style.display = 'none';
      lokasiKeluar.style.display = 'none';
    } else {
      jamFields.style.display = 'none';
      lokasiMasuk.style.display = 'none';
      lokasiKeluar.style.display = 'none';
    }
  });

      document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Flatpickr
        flatpickr("#jam_masuk", {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          time_24hr: true
        });
        flatpickr("#jam_keluar", {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          time_24hr: true
        });

        // Gunakan Lokasi Kantor
        const kantor = "-7.161892,112.621675";
        document.getElementById("btnLokasiMasuk")
                .addEventListener("click", () => {
          document.getElementById("lokasi_masuk").value = kantor;
        });
        document.getElementById("btnLokasiKeluar")
                .addEventListener("click", () => {
          document.getElementById("lokasi_keluar").value = kantor;
        });

        function validateForm() {
            let isValid = true;

            // Reset semua border input
            const inputs = document.querySelectorAll("#formPresensiManual input, #formPresensiManual select");
            inputs.forEach(input => {
                input.classList.remove("border-red-500");
            });

            // Periksa masing-masing input required
            inputs.forEach(input => {
                if (input.hasAttribute("required") && !input.value.trim()) {
                input.classList.add("border-red-500");
                isValid = false;
                }
            });

            return isValid;
            }


        // Konfirmasi sebelum submit
        document.getElementById("btnSubmit").addEventListener("click", function() {
            if (!validateForm()) {
                Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap!',
                text: 'Harap isi semua field yang wajib diisi.',
                confirmButtonText: 'OK'
                });
                return;
            }

          Swal.fire({
            title: 'Simpan presensi manual?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan!',
            cancelButtonText: 'Batal'
          }).then(result => {
            if (result.isConfirmed) {
              document.getElementById("formPresensiManual").submit();
            }
          });
        });

        // Error toast dari session
        @if(session('error'))
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        @endif
      });
    </script>
  </x-app-layout>
