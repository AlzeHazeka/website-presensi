<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Support\GeoPosition;

class LemburController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $lembur = Lembur::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();
        $izinHariIni = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();

        $hour = (int) $now->format('H');
        if ($hour < 12) {
            $ucapan = 'Selamat Pagi';
        } elseif ($hour < 18) {
            $ucapan = 'Selamat Siang';
        } else {
            $ucapan = 'Selamat Malam';
        }

        return Inertia::render('Lembur/Index', [
            'ucapan' => $ucapan,
            'tanggalHariIni' => $tanggalHariIni,
            'tanggalHariIniHuman' => Carbon::parse($tanggalHariIni)->translatedFormat('l, d F Y'),
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
            'izinHariIni' => $izinHariIni
                ? [
                    'id_izin' => $izinHariIni->id_izin,
                    'tanggal_izin' => $izinHariIni->tanggal_izin,
                ]
                : null,
            'lembur' => $lembur
                ? [
                    'id_lembur' => $lembur->id_lembur,
                    'tanggal' => $lembur->tanggal,
                    'jam_mulai_lembur' => $lembur->jam_mulai_lembur ? Carbon::parse($lembur->jam_mulai_lembur)->format('H:i') : null,
                    'lokasi_mulai_lembur' => $lembur->lokasi_mulai_lembur,
                    'jam_pulang_lembur' => $lembur->jam_pulang_lembur ? Carbon::parse($lembur->jam_pulang_lembur)->format('H:i') : null,
                    'lokasi_pulang_lembur' => $lembur->lokasi_pulang_lembur,
                ]
                : null,
        ]);
    }

    public function mulaiLembur(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa mulai lembur.']);
        }

        $existing = Lembur::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();
        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah mulai lembur hari ini!']);
        }

        $validated = $request->validate([
            'lokasi' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $position = GeoPosition::extract($validated);

        Lembur::create([
            'user_id' => $user->user_id,
            'tanggal' => $tanggalHariIni,
            // SERVER TIME AUTHORITATIVE
            'jam_mulai_lembur' => $now,
            // Backward compatible: tetap simpan string "lat, lng"
            'lokasi_mulai_lembur' => $position['lokasi_string'],
            // Metadata ringan (nullable, untuk audit)
            'lat_mulai_lembur' => $position['lat'],
            'lng_mulai_lembur' => $position['lng'],
            'accuracy_mulai_lembur' => $position['accuracy'],
            'ip_mulai_lembur' => $request->ip(),
            'ua_mulai_lembur' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lembur dimulai!']);
    }

    public function pulangLembur(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa menyelesaikan lembur.']);
        }

        $lembur = Lembur::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();

        if (! $lembur) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum mulai lembur!']);
        }

        if ($lembur->jam_pulang_lembur) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah selesai lembur hari ini!']);
        }

        $validated = $request->validate([
            'lokasi' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $position = GeoPosition::extract($validated);

        $lembur->update([
            // SERVER TIME AUTHORITATIVE
            'jam_pulang_lembur' => $now,
            // Backward compatible: tetap simpan string "lat, lng"
            'lokasi_pulang_lembur' => $position['lokasi_string'],
            // Metadata ringan (nullable, untuk audit)
            'lat_selesai_lembur' => $position['lat'],
            'lng_selesai_lembur' => $position['lng'],
            'accuracy_selesai_lembur' => $position['accuracy'],
            'ip_selesai_lembur' => $request->ip(),
            'ua_selesai_lembur' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lembur selesai!']);
    }

    // extractLokasiString dipindahkan ke App\Support\GeoPosition agar reusable untuk lembur/presensi.
}
