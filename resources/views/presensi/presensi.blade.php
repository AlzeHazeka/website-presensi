<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">📍 Halaman Presensi</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="rounded-lg bg-white p-6 shadow-md">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <p class="text-gray-600">
                        {{ $ucapan }}, <strong>{{ Auth::user()->nama }}</strong>
                    </p>
                    <p class="text-gray-600">
                        Hari ini: <strong>{{ \Carbon\Carbon::parse($tanggalHariIni)->translatedFormat('l, d F Y') }}</strong>
                    </p>
                </div>
                <a href="{{ route('presensi.riwayat') }}"
                   class="w-full md:w-auto text-center rounded-lg bg-sky-600 px-5 py-2 font-semibold text-white shadow-md transition hover:bg-sky-700">
                    Riwayat Presensi
                </a>
            </div>

            {{-- Peringatan Izin --}}
            @if ($izin)
                <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 font-semibold">
                    ⚠️ Anda sedang mengajukan izin untuk hari ini ({{ $izin->tanggal_pengajuan }}), presensi dan lembur dinonaktifkan.
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- PRESENSI MASUK -->
                <div class="rounded-lg bg-gray-100 p-4">
                    <h3 class="text-lg font-bold">Presensi Masuk</h3>
                    <p class="text-gray-600">
                        @if ($presensi && $presensi->jam_masuk)
                            ✅ Anda sudah presensi masuk pada {{ $presensi->jam_masuk }}
                        @elseif ($izin)
                            🚫 Anda sedang izin hari ini
                        @else
                            ❌ Anda belum presensi masuk
                        @endif
                    </p>
                    <p class="text-gray-600">Waktu masuk: <span id="jamMasuk">{{ $presensi->jam_masuk ?? '-' }}</span></p>
                    <button
                        id="btnMasuk"
                        onclick="ambilLokasi('masuk')"
                        class="mt-4 w-full rounded-lg bg-green-600 px-5 py-2 font-semibold text-white shadow-md transition hover:bg-green-700 disabled:opacity-50"
                        @if ($presensi && $presensi->jam_masuk || $izin) disabled @endif>
                        Presensi Masuk
                    </button>
                </div>

                <!-- PRESENSI KELUAR -->
                <div class="rounded-lg bg-gray-100 p-4">
                    <h3 class="text-lg font-bold">Presensi Keluar</h3>
                    <p class="text-gray-600">
                        @if ($presensi && $presensi->jam_keluar)
                            ✅ Anda sudah presensi keluar pada {{ $presensi->jam_keluar }}
                        @elseif ($izin)
                            🚫 Anda sedang izin hari ini
                        @else
                            ❌ Anda belum presensi keluar
                        @endif
                    </p>
                    <p class="text-gray-600">Waktu keluar: <span id="jamKeluar">{{ $presensi->jam_keluar ?? '-' }}</span></p>
                    <button
                        id="btnKeluar"
                        onclick="ambilLokasi('keluar')"
                        class="mt-4 w-full rounded-lg bg-red-600 px-5 py-2 font-semibold text-white shadow-md transition hover:bg-red-700 disabled:opacity-50"
                        @if (!$presensi || ($presensi && $presensi->jam_keluar) || $izin) disabled @endif>
                        Presensi Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Toast Notification -->

     <div x-data="{ show: false, message: '', type: 'success' }" x-show="show" x-transition.duration.300ms :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'" class="fixed right-5 bottom-5 flex items-center space-x-2 rounded-lg px-5 py-3 text-sm text-white shadow-lg" x-init="() => { if (show) setTimeout(() => show = false, 3000) }">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
        </svg>
        <span x-text="message"></span>
      </div>

      <!-- Sweet Alert -->
      <script>
        function showToast(message, type = 'success') {
                Swal.fire({
                    icon: type,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000
                });
            }

                document.addEventListener('alpine:init', () => {
                    Alpine.store('toast', {
                        show: false,
                        message: '',
                        type: 'success'
                    });
                });


                function ambilLokasi(jenis) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let lokasi = position.coords.latitude + ", " + position.coords.longitude;
                    let waktuLokal = new Date().toLocaleTimeString('id-ID', { hour12: false }); // Format 24 jam
                    kirimPresensi(jenis, lokasi, waktuLokal);
                }, function(error) {
                    showToast("Gagal mendapatkan lokasi! Izinkan akses lokasi.", "error");
                });
            } else {
                showToast("Browser tidak mendukung Geolocation!", "error");
            }
        }

        function kirimPresensi(jenis, lokasi, waktuLokal) {
            fetch(jenis === "masuk" ? "{{ route('presensi.masuk') }}" : "{{ route('presensi.keluar') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ lokasi: lokasi, waktu: waktuLokal })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, "success");
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(error => {
                showToast("Terjadi kesalahan!", "error");
            });
        }
      </script>
</x-app-layout>
