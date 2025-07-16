@php
    $user = Auth::user();
    $name = $user->nama ?? 'User';

    // Ambil jam lokal
    date_default_timezone_set('Asia/Jakarta');
    $hour = now()->hour;
    $hariIni = now()->translatedFormat('l, d F Y');

    if ($hour < 12) {
        $greeting = "Selamat Pagi, $name! 🌞";
    } elseif ($hour < 18) {
        $greeting = "Selamat Siang, $name! ☀️";
    } else {
        $greeting = "Selamat Malam, $name! 🌙";
    }
@endphp

<div class="space-y-6">
    <!-- Bagian Ucapan & Waktu -->
    <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold">{{ $greeting }}</h2>
        <p class="text-lg">Tetap semangat bekerja hari ini! 🚀</p>
        <p class="text-sm mt-2">Hari ini: <span class="font-semibold">{{ $hariIni }}</span></p>
        <p class="text-sm">Waktu sekarang: <span id="clock" class="font-mono text-lg"></span></p>
    </div>

    <!-- Status Presensi (Data dari Database) -->
    <div class="bg-white p-6 rounded-lg shadow-md border flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h3 class="text-xl font-semibold">Status Presensi 📋</h3>
            <p id="status-presensi" class="mt-2 font-semibold text-gray-700">⏳ Memuat data...</p>
        </div>
        <div class="w-full md:w-auto">
            <a href="{{ route('presensi.index') }}"
            id="btn-presensi"
            class="block w-full bg-green-500 text-white px-4 py-2 rounded-md shadow-md text-center hover:bg-green-600 hidden">
                Silakan Presensi
            </a>
        </div>
    </div>


    <!-- Statistik Kehadiran -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-md text-center">
            <h4 class="text-lg font-semibold">Kehadiran Bulan Ini</h4>
            <p id="jumlah-hadir" class="text-2xl font-bold text-blue-500">-</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md text-center">
            <h4 class="text-lg font-semibold">Total Hari Kerja</h4>
            <p id="total-hari-kerja" class="text-2xl font-bold text-gray-500">-</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md text-center">
            <h4 class="text-lg font-semibold">Persentase Kehadiran</h4>
            <p id="persentase-kehadiran" class="text-2xl font-bold text-green-500">-</p>
        </div>
    </div>
</div>

<!-- Skrip untuk Update Data Secara Live -->
<script>
    function updateClock() {
        const now = new Date();
        const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    async function fetchPresensiStatus() {
        try {
            const response = await fetch("{{ route('presensi.status') }}");
            const data = await response.json();

            const statusPresensi = document.getElementById('status-presensi');
            const btnPresensi = document.getElementById('btn-presensi');

            // 🛑 Prioritaskan pengecekan jika sedang izin
            if (data.izinHariIni) {
                statusPresensi.innerHTML = `🚫 Anda sedang izin hari ini.`;
                statusPresensi.className = "mt-2 font-semibold text-red-700";
                btnPresensi.classList.add('hidden');
            }
            else if (!data.sudahPresensiMasuk && !data.sudahPresensiKeluar) {
                statusPresensi.innerHTML = `⚠ Anda belum melakukan presensi hari ini!`;
                statusPresensi.className = "mt-2 font-semibold text-red-600";
                btnPresensi.classList.remove('hidden');
            }
            else if (data.sudahPresensiMasuk && !data.sudahPresensiKeluar) {
                statusPresensi.innerHTML = `✅ Anda sudah melakukan presensi masuk pada pukul <strong>${data.jamMasuk}</strong>, tetapi belum melakukan presensi keluar.`;
                statusPresensi.className = "mt-2 font-semibold text-yellow-600";
                btnPresensi.classList.remove('hidden');
            }
            else if (data.sudahPresensiMasuk && data.sudahPresensiKeluar) {
                statusPresensi.innerHTML = `✅ Anda telah presensi masuk pada pukul <strong>${data.jamMasuk}</strong> dan keluar pada pukul <strong>${data.jamKeluar}</strong>.`;
                statusPresensi.className = "mt-2 font-semibold text-green-600";
                btnPresensi.classList.add('hidden');
            }

            // Update statistik
            document.getElementById('jumlah-hadir').innerText = data.jumlahHadir;
            document.getElementById('total-hari-kerja').innerText = data.totalHariKerja;
            document.getElementById('persentase-kehadiran').innerText = data.persentaseKehadiran + "%";

        } catch (error) {
            console.error("Gagal mengambil data presensi:", error);
        }
    }

    fetchPresensiStatus();
</script>

<style>
    /* Responsif untuk sidebar dan konten */
    @media (max-width: 768px) {
        .space-y-6 {
            padding: 1rem;
        }
        .grid-cols-1 {
            grid-template-columns: 1fr;
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Sidebar disembunyikan di layar kecil */
    @media (max-width: 1024px) {
        .sidebar {
            display: none;
        }
    }
</style>
