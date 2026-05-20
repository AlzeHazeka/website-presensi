<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Support\IzinEligibility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $now = Carbon::now(config('app.timezone'));
        $tanggalHariIni = $now->toDateString();

        $monthParam = (string) $request->query('month', '');
        $activeMonth = null;
        if (preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            try {
                $activeMonth = Carbon::createFromFormat('Y-m', $monthParam, config('app.timezone'))->startOfMonth();
            } catch (\Throwable) {
                $activeMonth = null;
            }
        }
        $activeMonth = $activeMonth ?: $now->copy()->startOfMonth();

        $hour = (int) $now->format('H');
        if ($hour < 12) {
            $ucapan = 'Selamat Pagi';
        } elseif ($hour < 18) {
            $ucapan = 'Selamat Siang';
        } else {
            $ucapan = 'Selamat Malam';
        }

        return Inertia::render('Izin/Index', [
            'ucapan' => $ucapan,
            'tanggalHariIni' => $tanggalHariIni,
            'tanggalHariIniHuman' => Carbon::parse($tanggalHariIni)->translatedFormat('l, d F Y'),
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
            'activeMonth' => $activeMonth->format('Y-m'),
            'riwayatIzin' => IzinEligibility::monthlyHistory(
                $user->user_id,
                (int) $activeMonth->year,
                (int) $activeMonth->month,
                config('app.timezone'),
            ),
            'eligibilityHariIni' => IzinEligibility::check($user->user_id, $tanggalHariIni),
        ]);
    }

    public function eligibility(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $result = IzinEligibility::check($user->user_id, (string) $validated['date']);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function ajukan(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'tanggal_izin' => ['required', 'date', 'after_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $tanggalIzin = Carbon::parse($validated['tanggal_izin'])->toDateString();
        $keterangan = trim((string) ($validated['keterangan'] ?? ''));
        $keterangan = $keterangan !== '' ? $keterangan : null;

        $eligibility = IzinEligibility::check($user->user_id, $tanggalIzin);
        if ($eligibility['blocked_by_activity']) {
            return response()->json([
                'status' => 'error',
                'message' => $eligibility['activity_message'] ?? 'Pengajuan izin/cuti tidak tersedia pada tanggal ini.',
            ]);
        }

        if ($eligibility['has_izin']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah mengajukan izin untuk tanggal tersebut.',
            ]);
        }

        Izin::create([
            'user_id' => $user->user_id,
            'tanggal_pengajuan' => Carbon::now(config('app.timezone')),
            'tanggal_izin' => $tanggalIzin,
            'keterangan' => $keterangan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Izin berhasil diajukan.',
        ]);
    }
}
