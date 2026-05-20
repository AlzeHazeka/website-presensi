<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl leading-tight font-semibold text-gray-800">📅 Riwayat Presensi</h2>
            <div class="flex space-x-2">
                <select id="bulan" class="p-2 border rounded">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('m', $i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select id="tahun" class="p-2 border rounded">
                    @php
                        $tahunSekarang = now()->year;
                    @endphp
                    @for ($i = 2023; $i <= $tahunSekarang + 1; $i++)
                        <option value="{{ $i }}" {{ $i == $tahunSekarang ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>
    </x-slot>


    <div class="container mx-auto p-6">
        <div id="calendar"></div>

        <div class="p-4 bg-white shadow-md rounded-lg mt-6">
            <h3 class="text-lg font-bold">📅 Detail Presensi</h3>
            <p><strong>Tanggal:</strong> <span id="detailTanggal">Pilih tanggal</span></p>
            <p><strong>Jam Masuk:</strong> <span id="detailJamMasuk">-</span></p>
            <p><strong>Jam Keluar:</strong> <span id="detailJamKeluar">-</span></p>
            <p><strong>Lokasi Masuk:</strong> <span id="detailLokasiMasuk">-</span></p>
            <p><strong>Lokasi Keluar:</strong> <span id="detailLokasiKeluar">-</span></p>
        </div>

        <div id="gajiPresensi" class="mt-6 bg-white p-6 shadow-md rounded-lg">
            <h3 class="text-lg font-bold">💰 Penghitungan Gaji</h3>
            <p><strong>Total Hari Kerja:</strong> <span id="totalHariKerja">0</span> hari</p>
            <p><strong>Gaji per Hari:</strong> Rp <span id="gajiPerHari">0</span></p>
            <p><strong>Total Gaji:</strong> Rp <span id="totalGaji">0</span></p>
            <p class="text-red-500 font-bold" id="gajiPesan"></p>
        </div>
    </div>

    <!-- FullCalendar & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id', // Mengubah bahasa kalender ke Indonesia
                buttonText: {
                    today: "Hari Ini"
                },
                events: [
                    @foreach ($presensi as $p)
                    {
                        title: '{{ $p->jam_keluar ? "✅ Hadir" : "⚠️ Masuk Saja" }}',
                        start: '{{ $p->tanggal }}',
                        backgroundColor: '{{ $p->jam_keluar ? "#28a745" : "#ffc107" }}',
                        borderColor: '{{ $p->jam_keluar ? "#218838" : "#ff9800" }}',
                        textColor: 'white',
                        extendedProps: {
                            jamMasuk: '{{ $p->jam_masuk }}',
                            jamKeluar: '{{ $p->jam_keluar ?? "-" }}',
                            lokasiMasuk: '{{ $p->lokasi_masuk }}',
                            lokasiKeluar: '{{ $p->lokasi_keluar ?? "-" }}'
                        }
                    },
                    @endforeach
                ],
                eventClick: function(info) {
                    document.getElementById("detailTanggal").innerHTML = info.event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    document.getElementById("detailJamMasuk").innerHTML = info.event.extendedProps.jamMasuk;
                    document.getElementById("detailJamKeluar").innerHTML = info.event.extendedProps.jamKeluar;
                    document.getElementById("detailLokasiMasuk").innerHTML = `<a href="https://www.google.com/maps?q=${info.event.extendedProps.lokasiMasuk}" target="_blank" class="text-blue-500 underline">${info.event.extendedProps.lokasiMasuk}</a>`;
                    document.getElementById("detailLokasiKeluar").innerHTML = info.event.extendedProps.lokasiKeluar !== "-" ? `<a href="https://www.google.com/maps?q=${info.event.extendedProps.lokasiKeluar}" target="_blank" class="text-blue-500 underline">${info.event.extendedProps.lokasiKeluar}</a>` : "-";
                },
                datesSet: function(info) {
                    let newDate = info.view.currentStart;
                    let newMonth = newDate.getMonth() + 1;
                    let newYear = newDate.getFullYear();

                    document.getElementById('bulan').value = newMonth;
                    document.getElementById('tahun').value = newYear;

                    updateGaji(newMonth, newYear);
                }
            });
            calendar.render();

            document.getElementById('bulan').addEventListener('change', function() {
                let bulan = document.getElementById('bulan').value;
                let tahun = document.getElementById('tahun').value;
                let newDate = new Date(tahun, bulan - 1, 1);
                calendar.gotoDate(newDate);
                updateGaji(bulan, tahun);
            });

            document.getElementById('tahun').addEventListener('change', function() {
                let bulan = document.getElementById('bulan').value;
                let tahun = document.getElementById('tahun').value;
                let newDate = new Date(tahun, bulan - 1, 1);
                calendar.gotoDate(newDate);
                updateGaji(bulan, tahun);
            });

            function updateGaji(bulan, tahun) {
                $.ajax({
                    url: "{{ route('presensi.hitungGaji') }}",
                    type: "GET",
                    data: { bulan: bulan, tahun: tahun },
                    success: function(response) {
                        if (response.status === 'info') {
                            $('#totalHariKerja').text('-');
                            $('#gajiPerHari').text('-');
                            $('#totalGaji').text(response.total_gaji);
                            $('#gajiPesan').text(response.message);
                        } else {
                            $('#totalHariKerja').text(response.total_hari_kerja);
                            $('#gajiPerHari').text(response.gaji_per_hari);
                            $('#totalGaji').text(response.total_gaji);
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
