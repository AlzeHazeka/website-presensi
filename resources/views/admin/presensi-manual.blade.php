<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Presensi Manual
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form id="formPresensiManual" method="POST" action="{{ route('admin.presensi.store') }}">
                    @csrf

                    <!-- Pilih Karyawan -->
                    <div class="mb-4">
                        <label for="user_id" class="block font-semibold">Pilih Karyawan:</label>
                        <select name="user_id" id="user_id" class="w-full border rounded p-2" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->user_id }}">{{ $user->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Presensi -->
                    <div class="mb-4">
                        <label for="tanggal" class="block font-semibold">Tanggal:</label>
                        <input type="date" name="tanggal" id="tanggal" class="w-full border rounded p-2" required>
                    </div>

                    <!-- Jam Masuk -->
                    <div class="mb-4">
                        <label for="jam_masuk" class="block font-semibold">Jam Masuk:</label>
                        <input type="text" name="jam_masuk" id="jam_masuk" class="w-full border rounded p-2" required>
                    </div>

                    <!-- Lokasi Masuk -->
                    <div class="mb-4">
                        <label for="lokasi_masuk" class="block font-semibold">Lokasi Masuk:</label>
                        <div class="flex">
                            <input type="text" name="lokasi_masuk" id="lokasi_masuk" class="w-full border rounded p-2">
                            <button type="button" id="btnLokasiMasuk" class="ml-2 px-3 py-1 bg-gray-500 text-white rounded">Gunakan Lokasi Kantor</button>
                        </div>
                    </div>

                    <!-- Jam Keluar -->
                    <div class="mb-4">
                        <label for="jam_keluar" class="block font-semibold">Jam Keluar:</label>
                        <input type="text" name="jam_keluar" id="jam_keluar" class="w-full border rounded p-2" required>
                    </div>

                    <!-- Lokasi Keluar -->
                    <div class="mb-4">
                        <label for="lokasi_keluar" class="block font-semibold">Lokasi Keluar:</label>
                        <div class="flex">
                            <input type="text" name="lokasi_keluar" id="lokasi_keluar" class="w-full border rounded p-2">
                            <button type="button" id="btnLokasiKeluar" class="ml-2 px-3 py-1 bg-gray-500 text-white rounded">Gunakan Lokasi Kantor</button>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="mt-6">
                        <button type="button" id="btnSubmit" class="px-4 py-2 bg-green-600 text-white rounded">Simpan Presensi</button>
                        <a href="{{ route('admin.presensi.by-date') }}" class="px-4 py-2 bg-gray-500 text-white rounded">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 & Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Waktu Masuk & Keluar
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
        const lokasiKantor = "-7.161892,112.621675";
        document.getElementById("btnLokasiMasuk").addEventListener("click", function () {
            document.getElementById("lokasi_masuk").value = lokasiKantor;
        });
        document.getElementById("btnLokasiKeluar").addEventListener("click", function () {
            document.getElementById("lokasi_keluar").value = lokasiKantor;
        });

        // Konfirmasi sebelum submit
        document.getElementById("btnSubmit").addEventListener("click", function () {
            Swal.fire({
                title: 'Simpan presensi manual?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("formPresensiManual").submit();
                }
            });
        });

        // Tampilkan Error Toast jika ada dari session
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
    </script>
</x-app-layout>
