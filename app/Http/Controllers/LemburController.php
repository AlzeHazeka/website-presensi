<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lembur;
use Carbon\Carbon;

class LemburController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $tanggalHariIni = Carbon::now()->toDateString();
        $lembur = Lembur::where('user_id', $user->user_id)->whereDate('tanggal', $tanggalHariIni)->first();
        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();

        $hour = Carbon::now()->format('H');
        $ucapan = $hour < 12 ? 'Selamat Pagi' : ($hour < 18 ? 'Selamat Siang' : 'Selamat Malam');

        return view('lembur.lembur', compact('lembur', 'ucapan', 'tanggalHariIni', 'izin'));
    }


    public function mulaiLembur(Request $request)
    {
        $user = Auth::user();
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        // Cek apakah user sedang izin
        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa mulai lembur.']);
        }

        $lembur = Lembur::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();
        if ($lembur) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah mulai lembur hari ini!']);
        }

        Lembur::create([
            'user_id' => $user->user_id,
            'tanggal' => $tanggalHariIni,
            'jam_mulai_lembur' => $request->waktu,
            'lokasi_mulai_lembur' => $request->lokasi
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lembur dimulai!']);
    }


    public function pulangLembur(Request $request)
    {
        $user = Auth::user();
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        // Cek apakah user sedang izin
        $izin = Izin::where('user_id', $user->user_id)->whereDate('tanggal_izin', $tanggalHariIni)->first();
        if ($izin) {
            return response()->json(['status' => 'error', 'message' => 'Anda sedang izin hari ini, tidak bisa menyelesaikan lembur.']);
        }

        $lembur = Lembur::where('user_id', $user->user_id)->where('tanggal', $tanggalHariIni)->first();

        if (!$lembur) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum mulai lembur!']);
        }

        if ($lembur->jam_pulang_lembur) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah selesai lembur hari ini!']);
        }

        $lembur->update([
            'jam_pulang_lembur' => $request->waktu,
            'lokasi_pulang_lembur' => $request->lokasi
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lembur selesai!']);
    }
}
