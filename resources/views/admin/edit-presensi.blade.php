<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Presensi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form id="formPresensi" method="POST" action="{{ route('admin.presensi.update', $presensi->id_presensi) }}">
                    @csrf
                    <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

                    <!-- Nama Karyawan -->
                    <div class="mb-4">
                        <label class="block font-semibold">Nama Karyawan:</label>
                        <p class="text-gray-600">{{ $presensi->user->nama }}</p>
                    </div>

                    <!-- Tanggal Presensi -->
                    <div class="mb-4">
                        <label class="block font-semibold">Tanggal:</label>
                        <p class="text-gray-600">{{ \Carbon\Carbon::parse($presensi->tanggal)->translatedFormat('d F Y') }}</p>
                    </div>

                    <!-- Jam Masuk -->
                    <div class="mb-4">
                        <label for="jam_masuk" class="block font-semibold">Jam Masuk:</label>
                        <input type="text" name="jam_masuk" id="jam_masuk" class="w-full border rounded p-2"
                            value="{{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }}">
                    </div>

                    <!-- Lokasi Masuk -->
                    <div class="mb-4">
                        <label for="lokasi_masuk" class="block font-semibold">Lokasi Masuk:</label>
                        <div class="flex">
                            <input type="text" name="lokasi_masuk" id="lokasi_masuk" class="w-full border rounded p-2"
                                value="{{ old('lokasi_masuk', $presensi->lokasi_masuk) }}">
                            <button type="button" id="setLokasiMasuk" class="ml-2 px-3 py-1 bg-gray-500 text-white rounded">Gunakan Lokasi Kantor</button>
                        </div>
                    </div>

                    <!-- Jam Keluar -->
                    <div class="mb-4">
                        <label for="jam_keluar" class="block font-semibold">Jam Keluar:</label>
                        <input type="text" name="jam_keluar" id="jam_keluar" class="w-full border rounded p-2"
                            value="{{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') }}">
                    </div>

                    <!-- Lokasi Keluar -->
                    <div class="mb-4">
                        <label for="lokasi_keluar" class="block font-semibold">Lokasi Keluar:</label>
                        <div class="flex">
                            <input type="text" name="lokasi_keluar" id="lokasi_keluar" class="w-full border rounded p-2"
                                value="{{ old('lokasi_keluar', $presensi->lokasi_keluar) }}">
                            <button type="button" id="setLokasiKeluar" class="ml-2 px-3 py-1 bg-gray-500 text-white rounded">Gunakan Lokasi Kantor</button>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-6">
                        <button type="button" id="btnSubmit" class="px-4 py-2 bg-blue-500 text-white rounded">Simpan Perubahan</button>
                        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-500 text-white rounded">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tambahkan Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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

            // Konfirmasi sebelum submit
            document.getElementById("btnSubmit").addEventListener("click", function(event) {
                Swal.fire({
                    title: 'Yakin ingin menyimpan perubahan?',
                    text: "Pastikan data yang dimasukkan sudah benar!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Presensi berhasil diperbarui!',
                            showConfirmButton: false,
                            timer: 1500 // Tambahkan delay sebelum submit
                        }).then(() => {
                            document.getElementById("formPresensi").submit();
                        });
                    }
                });
            });


            console.log("Script SweetAlert dijalankan!");
            // Simpan session success di LocalStorage agar tidak hilang saat redirect
            @if(session('success'))
                console.log("Session success ditemukan di Blade:", "{{ session('success') }}");
                localStorage.setItem("successMessage", '{{ session("success") }}');
            @else
                console.log("Session success TIDAK ditemukan di Blade.");
            @endif

            // Cek apakah ada successMessage yang tersimpan di LocalStorage
            const successMessage = localStorage.getItem("successMessage");
            console.log("Isi localStorage:", successMessage);

            if (successMessage) {
                // Hapus pesan setelah diambil agar tidak muncul terus
                localStorage.removeItem("successMessage");

                // Tampilkan toast
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMessage,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    </script>
    <script>
        document.getElementById("setLokasiMasuk").addEventListener("click", function() {
            document.getElementById("lokasi_masuk").value = "-7.161892,112.621675";
        });

        document.getElementById("setLokasiKeluar").addEventListener("click", function() {
            document.getElementById("lokasi_keluar").value = "-7.161892,112.621675";
        });
    </script>


</x-app-layout>
