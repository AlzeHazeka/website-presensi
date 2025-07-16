<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="text-xl leading-tight font-semibold text-gray-800">📅 Riwayat Presensi</h2>
            <div class="flex flex-wrap gap-2">
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

    <div class="container mx-auto p-4 md:p-6">
        <div class="overflow-x-auto rounded-lg shadow-md bg-white p-4 mb-6">
            <div id="calendar"></div>
        </div>
            <div class="p-4 bg-white shadow-md rounded-lg">
                <h3 class="text-lg font-bold mb-2">📅 Detail Presensi</h3>
                <p><strong>Tanggal:</strong> <span id="detailTanggal">Pilih tanggal</span></p>
                <p><strong>Jam Masuk:</strong> <span id="detailJamMasuk">-</span></p>
                <p><strong>Jam Keluar:</strong> <span id="detailJamKeluar">-</span></p>
                <p><strong>Lokasi Masuk:</strong> <span id="detailLokasiMasuk">-</span></p>
                <p><strong>Lokasi Keluar:</strong> <span id="detailLokasiKeluar">-</span></p>

                <hr class="my-3">

                <h4 class="text-md font-semibold mb-1">⏰ Lembur</h4>
                <p><strong>Jam Mulai:</strong> <span id="detailJamMulaiLembur">-</span></p>
                <p><strong>Jam Pulang:</strong> <span id="detailJamPulangLembur">-</span></p>
                <p><strong>Lokasi Mulai:</strong> <span id="detailLokasiMulaiLembur">-</span></p>
                <p><strong>Lokasi Pulang:</strong> <span id="detailLokasiPulangLembur">-</span></p>
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
                displayEventTime: false,
                buttonText: {
                    today: "Hari Ini"
                },
                events: [
                    @foreach ($events as $e)
                    {
                        title: "{{ $e['title'] }}",
                        start: "{{ $e['start'] }}",
                        backgroundColor: "{{ $e['backgroundColor'] }}",
                        borderColor: "{{ $e['borderColor'] }}",
                        textColor: "{{ $e['textColor'] }}",
                        extendedProps: {
                            jamMasuk: "{{ $e['extendedProps']['jamMasuk'] }}",
                            jamKeluar: "{{ $e['extendedProps']['jamKeluar'] }}",
                            lokasiMasuk: "{{ $e['extendedProps']['lokasiMasuk'] }}",
                            lokasiKeluar: "{{ $e['extendedProps']['lokasiKeluar'] }}",
                            jamMulaiLembur: "{{ $e['extendedProps']['jamMulaiLembur'] }}",
                            jamPulangLembur: "{{ $e['extendedProps']['jamPulangLembur'] }}",
                            lokasiMulaiLembur: "{{ $e['extendedProps']['lokasiMulaiLembur'] }}",
                            lokasiPulangLembur: "{{ $e['extendedProps']['lokasiPulangLembur'] }}"
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

                     // Bagian lembur
                    document.getElementById("detailJamMulaiLembur").innerHTML = info.event.extendedProps.jamMulaiLembur;
                    document.getElementById("detailJamPulangLembur").innerHTML = info.event.extendedProps.jamPulangLembur;
                    document.getElementById("detailLokasiMulaiLembur").innerHTML = info.event.extendedProps.lokasiMulaiLembur !== "-" ? `<a href="https://www.google.com/maps?q=${info.event.extendedProps.lokasiMulaiLembur}" target="_blank" class="text-blue-500 underline">${info.event.extendedProps.lokasiMulaiLembur}</a>` : "-";
                    document.getElementById("detailLokasiPulangLembur").innerHTML = info.event.extendedProps.lokasiPulangLembur !== "-" ? `<a href="https://www.google.com/maps?q=${info.event.extendedProps.lokasiPulangLembur}" target="_blank" class="text-blue-500 underline">${info.event.extendedProps.lokasiPulangLembur}</a>` : "-";

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

    <style>
        /* Styling bawaan untuk header fullcalendar */
        .fc-header-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Penting! Supaya bisa turun ke bawah saat sempit */
            gap: 0.5rem; /* Jarak antar elemen */
        }

        /* Ini supaya tombol di kanan tetap rapi */
        .fc-header-toolbar .fc-toolbar-chunk:last-child {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsif saat layar kecil */
        @media (max-width: 640px) { /* Tailwind sm: breakpoint kira-kira 640px */
            .fc-header-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .fc-header-toolbar .fc-toolbar-chunk:last-child {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>

</x-app-layout>
