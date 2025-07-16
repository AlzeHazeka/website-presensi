<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Izin;
use App\Models\Presensi;
use App\Models\Lembur;
use Carbon\Carbon;

class IzinController extends Controller
{
    public function index()
    {
        $ucapan = now()->hour < 12 ? 'Selamat pagi' : (now()->hour < 17 ? 'Selamat siang' : 'Selamat malam');
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        $izinHariIni = Izin::where('user_id', Auth::id())
            ->whereDate('tanggal_izin', $tanggalHariIni)
            ->first();

        return view('izin.izin', compact('ucapan', 'tanggalHariIni', 'izinHariIni'));
    }

    public function ajukan(Request $request)
    {
        $request->validate([
            'tanggal_izin' => 'required|date|after_or_equal:today',
        ]);

        $tanggalIzin = Carbon::parse($request->tanggal_izin)->format('Y-m-d');
        $userId = Auth::id();

        // Cek presensi
        $sudahPresensi = Presensi::where('user_id', $userId)
            ->whereDate('tanggal', $tanggalIzin)
            ->exists();

        // Cek lembur
        $sudahLembur = Lembur::where('user_id', $userId)
            ->whereDate('tanggal', $tanggalIzin)
            ->exists();

        if ($sudahPresensi || $sudahLembur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan izin. Anda sudah melakukan presensi atau lembur pada tanggal tersebut.',
            ]);
        }

        // Cek sudah izin
        $sudahIzin = Izin::where('user_id', $userId)
            ->whereDate('tanggal_izin', $tanggalIzin)
            ->exists();

        if ($sudahIzin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah mengajukan izin untuk tanggal tersebut.',
            ]);
        }

        Izin::create([
            'user_id' => $userId,
            'tanggal_pengajuan' => now(),
            'tanggal_izin' => $tanggalIzin,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Izin berhasil diajukan.',
        ]);
    }
}
