<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapPresensiExport;
use App\Exports\PresensiByUserExport;
use App\Exports\PresensiByDateExport;
use App\Exports\LemburByUserExport;

class AdminPresensiController extends Controller
{
    public function index()
    {
        return view('admin.admin-presensi');
    }

    // 📌 1. Lihat Presensi Berdasarkan Tanggal
    public function presensiByDate(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? Carbon::today()->format('Y-m-d');

         $presensi = Presensi::whereDate('tanggal', $tanggal)
                ->with('user')
                ->orderBy('jam_masuk', 'asc')  // Urutkan berdasarkan jam masuk
                ->get();
        $users = User::all(); // Untuk menampilkan daftar semua karyawan

        return view('admin.by_date', compact('presensi', 'tanggal', 'users'));
    }

    // 📌 2. Lihat Presensi Berdasarkan Karyawan
    public function presensiByUser(Request $request)
    {
        $userId = $request->query('user_id');
        $bulan = (int) ($request->query('bulan') ?? now()->month);
        $tahun = (int) ($request->query('tahun') ?? now()->year);

        $users = User::all();

        if ($userId && !User::where('user_id', $userId)->exists()) {
            return redirect()->route('admin.presensi.by-user')->with('error', 'Karyawan tidak ditemukan.');
        }

        // Ambil presensi
        $presensiData = Presensi::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

        // Ambil izin
        $izinData = Izin::where('user_id', $userId)
            ->whereMonth('tanggal_izin', $bulan)
            ->whereYear('tanggal_izin', $tahun)
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal_izin)->format('Y-m-d'));

        // Ambil lembur
        $lembur = Lembur::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $presensi = [];

        for ($i = 1; $i <= $jumlahHari; $i++) {
            $tanggal = Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
            $hari = Carbon::create($tahun, $bulan, $i)->translatedFormat('l');

            $dataPresensi = $presensiData[$tanggal] ?? null;
            $dataIzin = $izinData[$tanggal] ?? null;

            $presensi[] = (object) [
                'id_presensi' => $dataPresensi->id_presensi ?? null,
                'tanggal' => $tanggal,
                'hari' => $hari,
                'jam_masuk' => $dataPresensi->jam_masuk ?? null,
                'lokasi_masuk' => $dataPresensi->lokasi_masuk ?? null,
                'jam_keluar' => $dataPresensi->jam_keluar ?? null,
                'lokasi_keluar' => $dataPresensi->lokasi_keluar ?? null,
                'izin' => $dataIzin ? $dataIzin->keterangan : null,
                'id_izin' => $dataIzin->id_izin ?? null,
            ];
        }

        return view('admin.by_user', compact(
            'users', 'presensi', 'lembur', 'bulan', 'tahun', 'userId', 'jumlahHari'
        ));
    }



    // 📌 3. Fitur Edit Data
    public function editPresensi($id)
    {
        session()->put('previous_url', url()->previous()); // Simpan URL sebelumnya
        $presensi = Presensi::findOrFail($id);
        return view('admin.edit-presensi', compact('presensi'));
    }
    public function updatePresensi(Request $request, $id)
    {
        $request->validate([
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
        ]);

        $presensi = Presensi::findOrFail($id);
        $presensi->update([
            'jam_masuk' => $request->jam_masuk,
            'lokasi_masuk' => $request->lokasi_masuk,
            'jam_keluar' => $request->jam_keluar,
            'lokasi_keluar' => $request->lokasi_keluar,
        ]);

        return redirect()->to($request->previous_url)->with('success', 'Presensi berhasil diperbarui!');
    }

    public function editLembur($id)
    {
        session()->put('previous_url', url()->previous()); // Simpan URL sebelumnya
        $lembur = Lembur::findOrFail($id);
        return view('admin.edit-lembur', compact('lembur'));
    }

    public function updateLembur(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_lembur' => 'required|date_format:H:i',
            'jam_pulang_lembur' => 'required|date_format:H:i|after:jam_mulai_lembur',
        ]);

        $lembur = Lembur::findOrFail($id);
        $lembur->update([
            'jam_mulai_lembur' => $request->jam_mulai_lembur,
            'lokasi_mulai_lembur' => $request->lokasi_mulai_lembur,
            'jam_pulang_lembur' => $request->jam_pulang_lembur,
            'lokasi_pulang_lembur' => $request->lokasi_pulang_lembur,
        ]);

        return redirect()->to($request->previous_url)->with('success', 'Data lembur berhasil diperbarui!');
    }

    public function createManualPresensi()
    {
        $users = User::all();
        return view('admin.presensi-manual', compact('users'));
    }

    public function storeManualPresensi(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'tanggal' => 'required|date',
            'tipe' => 'required|in:presensi,izin,lembur',
        ]);

        $userId = $request->user_id;
        $tanggal = $request->tanggal;
        $tipe = $request->tipe;

        // Cek keberadaan data lain pada tanggal tersebut
        $hasPresensi = Presensi::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();
        $hasIzin = Izin::where('user_id', $userId)->whereDate('tanggal_izin', $tanggal)->exists();
        $hasLembur = Lembur::where('user_id', $userId)->whereDate('tanggal', $tanggal)->exists();

        // 💡 Validasi lintas kondisi
        if ($tipe === 'izin') {
            if ($hasPresensi || $hasLembur) {
                return back()->with('error', 'Tidak bisa mengajukan izin karena sudah ada presensi atau lembur di tanggal tersebut.');
            }
        }

        if ($tipe === 'presensi') {
            if ($hasIzin) {
                return back()->with('error', 'Tidak bisa menambahkan presensi karena sudah ada izin di tanggal tersebut.');
            }

            if ($hasPresensi) {
                return back()->with('error', 'Presensi untuk tanggal ini sudah ada.');
            }

            $request->validate([
                'jam_masuk' => 'required|date_format:H:i',
                'jam_keluar' => 'nullable|date_format:H:i|after:jam_masuk',
            ]);

            Presensi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'jam_masuk' => $request->jam_masuk,
                'lokasi_masuk' => $request->lokasi_masuk,
                'jam_keluar' => $request->jam_keluar,
                'lokasi_keluar' => $request->lokasi_keluar,
            ]);
        }

        if ($tipe === 'lembur') {
            if ($hasIzin) {
                return back()->with('error', 'Tidak bisa menambahkan lembur karena sudah ada izin di tanggal tersebut.');
            }

            if (!$hasPresensi) {
                return back()->with('error', 'Tidak bisa menambahkan lembur karena belum ada presensi pada tanggal tersebut.');
            }

            if ($hasLembur) {
                return back()->with('error', 'Lembur untuk tanggal ini sudah ada.');
            }

            $request->validate([
                'jam_masuk' => 'required|date_format:H:i',
                'jam_keluar' => 'nullable|date_format:H:i|after:jam_masuk',
            ]);

            Lembur::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'jam_mulai_lembur' => $request->jam_masuk,
                'jam_pulang_lembur' => $request->jam_keluar,
                'lokasi_mulai_lembur' => $request->lokasi_masuk,
                'lokasi_pulang_lembur' => $request->lokasi_keluar,
            ]);
        }

        if ($tipe === 'izin') {
            Izin::create([
                'user_id' => $userId,
                'tanggal_izin' => $tanggal,
                'tanggal_pengajuan' => now(),
            ]);
        }

        return redirect()->route('admin.presensi.by-date')->with('success', 'Data berhasil ditambahkan!');
    }




    // 📌 5. Rekap Presensi

    public function rekapPresensi(Request $request)
    {
        \Carbon\Carbon::setLocale('id');

        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $users = User::all();

        $rekap = $users->map(function ($user) use ($bulan, $tahun) {
            $jumlah_presensi = $user->presensi()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->count();

            $jumlah_lembur = $user->lembur()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->count();

            $jumlah_izin = $user->izin()
                ->whereMonth('tanggal_izin', $bulan)
                ->whereYear('tanggal_izin', $tahun)
                ->count();

            $total_gaji = $user->tipe_gaji === 'harian'
                ? $jumlah_presensi * $user->gaji
                : $user->gaji;

            return [
                'nama' => $user->nama,
                'posisi' => $user->posisi,
                'status' => $user->status,
                'tipe_gaji' => $user->tipe_gaji,
                'gaji' => $user->gaji,
                'jumlah_presensi' => $jumlah_presensi,
                'jumlah_lembur' => $jumlah_lembur,
                'jumlah_izin' => $jumlah_izin,
                'total_gaji' => $total_gaji
            ];
        });

        return view('admin.rekap-presensi', compact('rekap', 'bulan', 'tahun'));
    }


    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $users = User::all();

        $rekap = $users->map(function ($user) use ($bulan, $tahun) {
            $jumlah_presensi = $user->presensi()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->count();

            $jumlah_lembur = $user->lembur()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->count();

            $jumlah_izin = $user->izin()
                ->whereMonth('tanggal_izin', $bulan)
                ->whereYear('tanggal_izin', $tahun)
                ->count();

            /*$total_gaji = $user->tipe_gaji === 'harian'
                ? $jumlah_presensi * $user->gaji
                : $user->gaji;*/

            return [
                'nama' => $user->nama,
                'role' => $user->role,
                'status' => $user->status,
                'tipe_gaji' => $user->tipe_gaji,
                'gaji' => $user->gaji,
                'jumlah_presensi' => $jumlah_presensi,
                'jumlah_lembur' => $jumlah_lembur,
                'jumlah_izin' => $jumlah_izin,
            ];
        });

        $fileName = "rekap-presensi-{$bulan}-{$tahun}.xlsx";
        return Excel::download(new RekapPresensiExport($rekap, $bulan, $tahun), $fileName);
    }


    public function exportByUserExcel(Request $request)
    {
        $user_id = $request->user_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $tab = $request->tab ?? 'presensi';

        $user = User::findOrFail($user_id);

        if ($tab === 'lembur') {
            $lembur = $user->lembur()
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            return Excel::download(
                new LemburByUserExport($user, $lembur, $bulan, $tahun),
                'lembur-' . $user->nama . '.xlsx'
            );
        }

        $presensi = $user->presensi()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->keyBy(fn($p) => \Carbon\Carbon::parse($p->tanggal)->format('Y-m-d'));

        $izin = $user->izin()
            ->whereMonth('tanggal_izin', $bulan)
            ->whereYear('tanggal_izin', $tahun)
            ->get()
            ->keyBy(fn($i) => \Carbon\Carbon::parse($i->tanggal_izin)->format('Y-m-d'));

        return Excel::download(
            new PresensiByUserExport($user, $presensi, $izin, $bulan, $tahun),
            'presensi-' . $user->nama . '.xlsx'
        );
    }

    public function exportByDateExcel(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $fileName = "presensi_{$request->tanggal}.xlsx";

        return Excel::download(
            new PresensiByDateExport($request->tanggal),
            $fileName
        );
    }


    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);
        $presensi->delete();

        // Kembali ke halaman sebelumnya dengan flash message
        return redirect()->back()->with('success', 'Data presensi berhasil dihapus!');
    }

    public function destroyLembur($id)
    {
        $lembur = Lembur::findOrFail($id);
        $lembur->delete();

        // Kembali ke halaman sebelumnya dengan flash message
        return redirect()->back()->with('success', 'Data Lembur berhasil dihapus!');
    }

    public function destroyIzin($id)
    {
        $izin = Izin::findOrFail($id);
        $izin->delete();

        // Kembali ke halaman sebelumnya dengan flash message
        return redirect()->back()->with('success', 'Data Izin berhasil dihapus!');
    }

}

