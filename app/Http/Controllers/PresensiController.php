<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Support\GeoPosition;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $now = Carbon::now(config('app.timezone'));

        // Ambil data presensi hari ini berdasarkan user yang login
        $tanggalHariIni = $now->toDateString();
        $presensi = Presensi::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();
        $izinHariIni = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();

        // Buat ucapan sesuai waktu
        $hour = $now->format('H');
        if ($hour < 12) {
            $ucapan = "Selamat Pagi";
        } elseif ($hour < 18) {
            $ucapan = "Selamat Siang";
        } else {
            $ucapan = "Selamat Malam";
        }

        // Operational summary (ringan)
        $bulan = (int) $now->month;
        $tahun = (int) $now->year;
        $totalHariBulanIni = (int) $now->daysInMonth;

        $totalHadirBulanIni = Presensi::where('user_id', $user->user_id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereNotNull('jam_masuk')
            ->count();

        $presensiLengkapBulanIni = Presensi::where('user_id', $user->user_id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereNotNull('jam_masuk')
            ->whereNotNull('jam_keluar')
            ->count();

        $completed = Presensi::where('user_id', $user->user_id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereNotNull('jam_masuk')
            ->whereNotNull('jam_keluar')
            ->get(['jam_masuk', 'jam_keluar']);

        $totalMinutes = 0;
        $completedCount = 0;
        foreach ($completed as $item) {
            $rawMasuk = $item->getRawOriginal('jam_masuk');
            $rawKeluar = $item->getRawOriginal('jam_keluar');
            if (!$rawMasuk || !$rawKeluar) {
                continue;
            }
            $diff = Carbon::parse($rawMasuk)->diffInMinutes(Carbon::parse($rawKeluar));
            if ($diff > 0 && $diff < (24 * 60)) {
                $totalMinutes += $diff;
                $completedCount++;
            }
        }

        $avgMinutes = $completedCount > 0 ? (int) round($totalMinutes / $completedCount) : null;

        $latest = Presensi::where('user_id', $user->user_id)
            ->whereNotNull('jam_masuk')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->first();

        return Inertia::render('Presensi/Index', [
            'ucapan' => $ucapan,
            'tanggalHariIni' => $tanggalHariIni,
            'tanggalHariIniHuman' => Carbon::parse($tanggalHariIni)->translatedFormat('l, d F Y'),
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
            'izinHariIni' => $izinHariIni
                ? [
                    'id_izin' => $izinHariIni->id_izin,
                    'tanggal_pengajuan' => $izinHariIni->tanggal_pengajuan,
                    'tanggal_izin' => $izinHariIni->tanggal_izin,
                ]
                : null,
            'presensi' => $presensi
                ? [
                    'id_presensi' => $presensi->id_presensi ?? null,
                    'tanggal' => $presensi->tanggal,
                    'jam_masuk' => $presensi->jam_masuk,
                    'lokasi_masuk' => $presensi->lokasi_masuk,
                    'lat_masuk' => $presensi->lat_masuk ?? null,
                    'lng_masuk' => $presensi->lng_masuk ?? null,
                    'accuracy_masuk' => $presensi->accuracy_masuk ?? null,
                    'jam_keluar' => $presensi->jam_keluar,
                    'lokasi_keluar' => $presensi->lokasi_keluar,
                    'lat_keluar' => $presensi->lat_keluar ?? null,
                    'lng_keluar' => $presensi->lng_keluar ?? null,
                    'accuracy_keluar' => $presensi->accuracy_keluar ?? null,
                ]
                : null,
            'summary' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'totalHariBulanIni' => $totalHariBulanIni,
                'totalHadirBulanIni' => $totalHadirBulanIni,
                'presensiLengkapBulanIni' => $presensiLengkapBulanIni,
                'avgMinutesBekerja' => $avgMinutes,
                'lokasiTerakhir' => $latest
                    ? [
                        'tanggal' => $latest->tanggal,
                        'jam_masuk' => $latest->jam_masuk,
                        'lokasi_masuk' => $latest->lokasi_masuk,
                        'lat_masuk' => $latest->lat_masuk ?? null,
                        'lng_masuk' => $latest->lng_masuk ?? null,
                        'accuracy_masuk' => $latest->accuracy_masuk ?? null,
                    ]
                    : null,
            ],
        ]);
    }

    public function presensiMasuk(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa melakukan presensi.']);
        }

        $validated = $request->validate([
            'lokasi' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $presensi = Presensi::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();

        if ($presensi) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah presensi masuk hari ini!']);
        }

        $position = GeoPosition::extract($validated);

        $presensi = Presensi::create([
            'user_id' => $user->user_id,
            'tanggal' => $tanggalHariIni,
            // SERVER TIME AUTHORITATIVE (anti manipulasi jam device)
            'jam_masuk' => $now,
            // Backward compatible: tetap simpan string "lat, lng"
            'lokasi_masuk' => $position['lokasi_string'],
            // Metadata ringan (nullable, untuk audit)
            'lat_masuk' => $position['lat'],
            'lng_masuk' => $position['lng'],
            'accuracy_masuk' => $position['accuracy'],
            'ip_masuk' => $request->ip(),
            'ua_masuk' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Presensi masuk berhasil!']);
    }

    public function presensiKeluar(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa presensi.']);
        }

        $validated = $request->validate([
            'lokasi' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $presensi = Presensi::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();

        if (!$presensi) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum presensi masuk!']);
        }

        if ($presensi->jam_keluar) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah presensi keluar hari ini!']);
        }

        $position = GeoPosition::extract($validated);

        $presensi->update([
            // SERVER TIME AUTHORITATIVE (anti manipulasi jam device)
            'jam_keluar' => $now,
            // Backward compatible: tetap simpan string "lat, lng"
            'lokasi_keluar' => $position['lokasi_string'],
            // Metadata ringan (nullable, untuk audit)
            'lat_keluar' => $position['lat'],
            'lng_keluar' => $position['lng'],
            'accuracy_keluar' => $position['accuracy'],
            'ip_keluar' => $request->ip(),
            'ua_keluar' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Presensi keluar berhasil!']);
    }

    // extractPosition dipindahkan ke App\Support\GeoPosition agar reusable untuk lembur/presensi.

    public function riwayat()
    {
        $user = Auth::user();

        $presensi = Presensi::where('user_id', $user->user_id)->orderBy('tanggal', 'desc')->get();
        $izin = Izin::where('user_id', $user->user_id)->orderBy('tanggal_izin', 'desc')->get();
        $lembur = Lembur::where('user_id', $user->user_id)->orderBy('tanggal', 'desc')->get();

        return Inertia::render('Presensi/Riwayat', [
            'presensi' => $presensi->map(fn (Presensi $item) => [
                'id_presensi' => $item->id_presensi ?? null,
                'tanggal' => $item->tanggal?->toDateString(),
                'jam_masuk' => $item->jam_masuk,
                'lokasi_masuk' => $item->lokasi_masuk,
                'lat_masuk' => $item->lat_masuk,
                'lng_masuk' => $item->lng_masuk,
                'accuracy_masuk' => $item->accuracy_masuk,
                'jam_keluar' => $item->jam_keluar,
                'lokasi_keluar' => $item->lokasi_keluar,
                'lat_keluar' => $item->lat_keluar,
                'lng_keluar' => $item->lng_keluar,
                'accuracy_keluar' => $item->accuracy_keluar,
            ]),
            // Izin dan cuti digabung sebagai satu kategori operasional (izin/cuti).
            'izinCuti' => $izin->map(fn (Izin $item) => [
                'id_izin' => $item->id_izin ?? null,
                'tanggal_pengajuan' => $item->tanggal_pengajuan?->toIso8601String(),
                'tanggal_izin' => $item->tanggal_izin?->toDateString(),
            ]),
            'lembur' => $lembur->map(fn (Lembur $item) => [
                'id_lembur' => $item->id_lembur ?? null,
                'tanggal' => $item->tanggal?->toDateString(),
                'jam_mulai_lembur' => $item->jam_mulai_lembur ? Carbon::parse($item->getRawOriginal('jam_mulai_lembur'))->format('H:i') : null,
                'lokasi_mulai_lembur' => $item->lokasi_mulai_lembur,
                'lat_mulai_lembur' => $item->lat_mulai_lembur ?? null,
                'lng_mulai_lembur' => $item->lng_mulai_lembur ?? null,
                'accuracy_mulai_lembur' => $item->accuracy_mulai_lembur ?? null,
                'jam_pulang_lembur' => $item->jam_pulang_lembur ? Carbon::parse($item->getRawOriginal('jam_pulang_lembur'))->format('H:i') : null,
                'lokasi_pulang_lembur' => $item->lokasi_pulang_lembur,
                'lat_selesai_lembur' => $item->lat_selesai_lembur ?? null,
                'lng_selesai_lembur' => $item->lng_selesai_lembur ?? null,
                'accuracy_selesai_lembur' => $item->accuracy_selesai_lembur ?? null,
            ]),
        ]);
    }


    public function getEvents()
    {
        $user = Auth::user();
        $presensi = Presensi::where('user_id', $user->user_id)->get();

        $events = [];

        foreach ($presensi as $p) {
            $warna = ($p->jam_masuk && $p->jam_keluar) ? '#10B981' : '#F59E0B'; // Hijau jika lengkap, Kuning jika hanya masuk
            $events[] = [
                'title' => '✅ Presensi',
                'start' => $p->tanggal,
                'backgroundColor' => $warna,
                'borderColor' => $warna
            ];
        }

        return response()->json($events);
    }

    public function getDetail(Request $request)
    {
        $user = Auth::user();
        $tanggal = $request->tanggal;

        $presensi = Presensi::where('user_id', $user->user_id)
                            ->where('tanggal', $tanggal)
                            ->first();

        return response()->json([
            'jam_masuk' => $presensi->jam_masuk ?? null,
            'jam_keluar' => $presensi->jam_keluar ?? null,
            'lokasi_masuk' => $presensi->lokasi_masuk ?? null,
            'lokasi_keluar' => $presensi->lokasi_keluar ?? null,
        ]);
    }

    public function hitungGaji(Request $request)
    {
        $user = Auth::user();

        // Ambil bulan dan tahun dari request (default: bulan dan tahun sekarang)
        $bulan = $request->bulan ?? Carbon::now()->format('m');
        $tahun = $request->tahun ?? Carbon::now()->format('Y');

        // Ambil informasi gaji user
        $gaji_per_hari = $user->gaji;
        $tipe_gaji = $user->tipe_gaji; // "harian" atau "bulanan"

        // Jika gaji bulanan, langsung kembalikan respons
        if ($tipe_gaji === 'bulanan') {
            return response()->json([
                'status' => 'info',
                'message' => 'Gaji Anda adalah bulanan.',
                'total_gaji' => $gaji_per_hari
            ]);
        }

        // Hitung total hari kerja berdasarkan bulan yang dipilih
        $total_hari_kerja = Presensi::where('user_id', $user->user_id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereNotNull('jam_masuk')
            ->whereNotNull('jam_keluar')
            ->count();

        // Hitung total gaji berdasarkan hari kerja
        $total_gaji = $total_hari_kerja * $gaji_per_hari;

        return response()->json([
            'total_hari_kerja' => $total_hari_kerja,
            'gaji_per_hari' => $gaji_per_hari,
            'total_gaji' => $total_gaji
        ]);
    }


    public function formatTanggalIndonesia($tanggal)
    {
        setlocale(LC_TIME, 'id_ID');
        return \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y');
    }

    public function getPresensiStatus()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Ambil presensi hari ini
        $presensiHariIni = Presensi::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        // Hitung jumlah kehadiran bulan ini
        $bulanIni = Carbon::now()->month;
        $totalHariKerja = Carbon::now()->daysInMonth;
        $jumlahHadir = Presensi::where('user_id', $userId)
            ->whereMonth('tanggal', $bulanIni)
            ->count();

        $persentaseKehadiran = $jumlahHadir > 0 ? round(($jumlahHadir / $totalHariKerja) * 100, 2) : 0;

        return response()->json([
            'sudahPresensiMasuk' => $presensiHariIni && $presensiHariIni->jam_masuk ? true : false,
            'sudahPresensiKeluar' => $presensiHariIni && $presensiHariIni->jam_keluar ? true : false,
            'jamMasuk' => $presensiHariIni && $presensiHariIni->jam_masuk ? Carbon::parse($presensiHariIni->jam_masuk)->format('H:i') : null,
            'jamKeluar' => $presensiHariIni && $presensiHariIni->jam_keluar ? Carbon::parse($presensiHariIni->jam_keluar)->format('H:i') : null,
            'jumlahHadir' => $jumlahHadir,
            'totalHariKerja' => $totalHariKerja,
            'persentaseKehadiran' => $persentaseKehadiran
        ]);
    }
}
