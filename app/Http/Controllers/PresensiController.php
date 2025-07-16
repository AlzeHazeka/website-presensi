<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Presensi;
use App\Models\Lembur;
use App\Models\Izin;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data presensi hari ini berdasarkan user yang login
        $tanggalHariIni = Carbon::now()->toDateString();
        $presensi = Presensi::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();
        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        // Buat ucapan sesuai waktu
        $hour = Carbon::now()->format('H');
        if ($hour < 12) {
            $ucapan = "Selamat Pagi";
        } elseif ($hour < 18) {
            $ucapan = "Selamat Siang";
        } else {
            $ucapan = "Selamat Malam";
        }

        return view('presensi.presensi', compact('presensi', 'ucapan', 'tanggalHariIni','izin'));
    }

    public function presensiMasuk(Request $request)
    {
        $user = Auth::user();
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        // Cek izin
        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa melakukan presensi.']);
        }

        $presensi = Presensi::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();
        if ($presensi) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah presensi masuk hari ini!']);
        }

        Presensi::create([
            'user_id' => $user->user_id,
            'tanggal' => $tanggalHariIni,
            'jam_masuk' => $request->waktu,
            'lokasi_masuk' => $request->lokasi
        ]);

        return response()->json(['status' => 'success', 'message' => 'Presensi masuk berhasil!']);
    }


    public function presensiKeluar(Request $request)
    {
        $user = Auth::user();
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa presensi.']);
        }

        $presensi = Presensi::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();

        if (!$presensi) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum presensi masuk!']);
        }

        if ($presensi->jam_keluar) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah presensi keluar hari ini!']);
        }

        $presensi->update([
            'jam_keluar' => $request->waktu,
            'lokasi_keluar' => $request->lokasi
        ]);

        return response()->json(['status' => 'success', 'message' => 'Presensi keluar berhasil!']);
    }


    public function riwayat()
    {
        $user = Auth::user();

        // Ambil semua data
        $presensi = Presensi::where('user_id', $user->user_id)->get()->keyBy('tanggal');
        $lembur = Lembur::where('user_id', $user->user_id)->get()->keyBy('tanggal');
        $izin = Izin::where('user_id', $user->user_id)->get()->keyBy('tanggal_izin');

        // Gabungkan semua tanggal unik dari ketiga jenis
        $tanggalGabungan = $presensi->keys()
            ->merge($lembur->keys())
            ->merge($izin->keys())
            ->unique()
            ->sort();

        $events = collect();

        foreach ($tanggalGabungan as $tanggal) {
            $p = $presensi->get($tanggal);
            $l = $lembur->get($tanggal);
            $i = $izin->get($tanggal);

            $judul = [];
            $warna = '#6B7280'; // Default abu-abu (abu Tailwind)

            if ($p) {
                $judul[] = $p->jam_keluar ? '✅ Hadir' : '⚠️ Masuk Saja';
                $warna = $p->jam_keluar ? '#28a745' : '#ffc107';
            }

            if ($l) {
                $judul[] = '🕒 Lembur';
                $warna = '#007bff'; // Override ke biru jika ada lembur
            }

            if ($i) {
            $judul[] = '📝 Izin';
            $warna = '#9CA3AF';}

            $events->push([
                'title' => implode(' + ', $judul),
                'start' => $tanggal,
                'backgroundColor' => $warna,
                'borderColor' => $warna,
                'textColor' => 'white',
                'extendedProps' => [
                    'jamMasuk' => $p->jam_masuk ?? '-',
                    'jamKeluar' => $p->jam_keluar ?? '-',
                    'lokasiMasuk' => $p->lokasi_masuk ?? '-',
                    'lokasiKeluar' => $p->lokasi_keluar ?? '-',
                    'jamMulaiLembur' => $l->jam_mulai_lembur ?? '-',
                    'jamPulangLembur' => $l->jam_pulang_lembur ?? '-',
                    'lokasiMulaiLembur' => $l->lokasi_mulai_lembur ?? '-',
                    'lokasiPulangLembur' => $l->lokasi_pulang_lembur ?? '-',
                    'statusIzin' => $i ? 'Izin' : null,
                ]
            ]);
        }

        return view('presensi.riwayat', [
            'events' => $events
        ]);
    }

    public function getEvents()
    {
        $user = Auth::user();
        $presensi = Presensi::where('user_id', $user->user_id)->get();
        $lembur = DB::table('lembur')->where('user_id', $user->user_id)->get();

        $events = [];

        // Event Presensi
        foreach ($presensi as $p) {
            $warna = ($p->jam_masuk && $p->jam_keluar) ? '#10B981' : '#F59E0B'; // Hijau atau kuning
            $events[] = [
                'title' => '✅ Presensi',
                'start' => $p->tanggal,
                'backgroundColor' => $warna,
                'borderColor' => $warna
            ];
        }

        // Event Lembur
        foreach ($lembur as $l) {
            $events[] = [
                'title' => '⏰ Lembur',
                'start' => $l->tanggal,
                'backgroundColor' => '#3B82F6', // Biru
                'borderColor' => '#3B82F6'
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

        $lembur = DB::table('lembur')
                    ->where('user_id', $user->user_id)
                    ->where('tanggal', $tanggal)
                    ->first();

        return response()->json([
            'jam_masuk' => $presensi->jam_masuk ?? null,
            'jam_keluar' => $presensi->jam_keluar ?? null,
            'lokasi_masuk' => $presensi->lokasi_masuk ?? null,
            'lokasi_keluar' => $presensi->lokasi_keluar ?? null,
            'jam_mulai_lembur' => $lembur->jam_mulai_lembur ?? null,
            'jam_pulang_lembur' => $lembur->jam_pulang_lembur ?? null,
            'lokasi_mulai_lembur' => $lembur->lokasi_mulai_lembur ?? null,
            'lokasi_pulang_lembur' => $lembur->lokasi_pulang_lembur ?? null,
        ]);
    }

    public function getPresensiStatus()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        $presensiHariIni = Presensi::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        $lemburHariIni = DB::table('lembur')
            ->where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        // ✅ Cek apakah user sedang izin hari ini
        $izinHariIni = DB::table('izin')
            ->where('user_id', $userId)
            ->whereDate('tanggal_izin', $today)
            ->exists();

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
            'adaLemburHariIni' => $lemburHariIni ? true : false,
            'jamMulaiLembur' => $lemburHariIni && $lemburHariIni->jam_mulai_lembur ? Carbon::parse($lemburHariIni->jam_mulai_lembur)->format('H:i') : null,
            'jamPulangLembur' => $lemburHariIni && $lemburHariIni->jam_pulang_lembur ? Carbon::parse($lemburHariIni->jam_pulang_lembur)->format('H:i') : null,
            'jumlahHadir' => $jumlahHadir,
            'totalHariKerja' => $totalHariKerja,
            'persentaseKehadiran' => $persentaseKehadiran,

            // ✅ Tambahkan flag ini
            'izinHariIni' => $izinHariIni,
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
}

