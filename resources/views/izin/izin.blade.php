<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">📄 Halaman Izin</h2>
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

            <div class="mt-6">
                <h3 class="text-lg font-bold mb-2">Ajukan Izin</h3>
                @if ($izinHariIni)
                    <p class="text-green-700">✅ Anda sudah mengajukan izin untuk hari ini.</p>
                @else
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <input type="date" id="tanggal_izin"
                            class="rounded border-gray-300 shadow-sm"
                            value="{{ $tanggalHariIni }}"
                            min="{{ $tanggalHariIni }}">
                        <button onclick="ajukanIzin()"
                                class="rounded-lg bg-yellow-500 px-5 py-2 font-semibold text-white shadow-md hover:bg-yellow-600">
                            Ajukan Izin
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function ajukanIzin() {
            const tanggal = document.getElementById('tanggal_izin').value;

            fetch("{{ route('izin.ajukan') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ tanggal_izin: tanggal })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.status,
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
                if (data.status === 'success') {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal mengajukan izin.',
                    text: error.message || 'Terjadi kesalahan saat mengirim data.'
                });
            });
        }
    </script>
</x-app-layout>
