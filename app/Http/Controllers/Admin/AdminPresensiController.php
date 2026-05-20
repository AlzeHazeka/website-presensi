<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Izin;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade as PDF;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Exports\RekapPresensiExport;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Inertia\Inertia;
use App\Exports\PresensiByDateExport;
use App\Support\MonthlyPresensiRekap;
use App\Support\OfficeLocation;
use App\Support\ManualOperationalInput;

class AdminPresensiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Presensi/Index');
    }

    // 📌 1. Lihat Presensi Berdasarkan Tanggal
    public function presensiByDate(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? Carbon::today()->format('Y-m-d');

        $presensi = Presensi::whereDate('tanggal', $tanggal)->with('user')->get();
        $izinCount = Izin::whereDate('tanggal_izin', $tanggal)->count();
        $lemburByUser = Lembur::whereDate('tanggal', $tanggal)->get(['user_id'])->groupBy('user_id');
        $lemburCount = $lemburByUser->count();

        $belumCheckoutCount = $presensi->filter(fn (Presensi $item) => $item->jam_masuk && ! $item->jam_keluar)->count();
        $hadirLengkapCount = $presensi->filter(fn (Presensi $item) => $item->jam_masuk && $item->jam_keluar)->count();

        return Inertia::render('Admin/Presensi/ByDate', [
            'tanggal' => $tanggal,
            'presensi' => $presensi->map(function (Presensi $item) {
                $diffInMinutes = $item->jam_masuk && $item->jam_keluar
                    ? Carbon::parse($item->jam_masuk)->diffInMinutes(Carbon::parse($item->jam_keluar))
                    : 0;

                $hours = (int) floor($diffInMinutes / 60);
                $minutes = (int) ($diffInMinutes % 60);

                return [
                    'id_presensi' => $item->id_presensi,
                    'user_id' => $item->user_id,
                    'nama' => $item->user?->nama,
                    'jam_masuk' => $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : null,
                    'lokasi_masuk' => $item->lokasi_masuk,
                    'jam_keluar' => $item->jam_keluar ? Carbon::parse($item->jam_keluar)->format('H:i') : null,
                    'lokasi_keluar' => $item->lokasi_keluar,
                    'total_jam_text' => $diffInMinutes > 0 ? "{$hours} Jam {$minutes} Menit" : '-',
                    'total_minutes' => $diffInMinutes,
                ];
            }),
            'total' => $presensi->count(),
            'summary' => [
                'total_hadir' => $presensi->count(),
                'total_hadir_lengkap' => $hadirLengkapCount,
                'total_belum_checkout' => $belumCheckoutCount,
                'total_izin_cuti' => $izinCount,
                'total_lembur' => $lemburCount,
            ],
            'lemburUserIds' => $lemburByUser->keys()->values()->all(),
        ]);
    }

    // 📌 2. Lihat Presensi Berdasarkan Karyawan
    public function presensiByUser(Request $request)
    {
        $userId = $request->query('user_id');
        $bulan = (int) ($request->query('bulan') ?? now()->month);
        $tahun = (int) ($request->query('tahun') ?? now()->year);

        // Ambil daftar karyawan untuk selector (urutkan agar mudah dicari)
        $users = User::orderBy('nama')->get();

        // Pastikan user_id valid
        if ($userId && !User::where('user_id', $userId)->exists()) {
            return redirect()->route('admin.presensi.by-user')->with('error', 'Karyawan tidak ditemukan.');
        }

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        $selectedUser = $userId ? User::where('user_id', $userId)->first() : null;

        // Jika belum pilih karyawan, return presensi kosong (UI akan tampil empty state)
        if (! $userId) {
            return Inertia::render('Admin/Presensi/ByUser', [
                'users' => $users->map(fn (User $user) => [
                    'user_id' => $user->user_id,
                    'nama' => $user->nama,
                    'posisi' => $user->posisi,
                    'role' => $user->role,
                    'status' => $user->status,
                ])->values(),
                'selectedUser' => null,
                'presensi' => [],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'userId' => null,
                'jumlahHari' => $jumlahHari,
                'izinDates' => [],
                'lemburDates' => [],
            ]);
        }

        // Ambil presensi berdasarkan user, bulan, dan tahun
        $presensiData = Presensi::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

        $izinDates = Izin::where('user_id', $userId)
            ->whereMonth('tanggal_izin', $bulan)
            ->whereYear('tanggal_izin', $tahun)
            ->pluck('tanggal_izin')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->all();

        $lemburDates = Lembur::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->pluck('tanggal')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->all();

        $presensi = [];
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $tanggal = Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
            $hari = Carbon::create($tahun, $bulan, $i)->translatedFormat('l');
            $data = $presensiData[$tanggal] ?? null;

            $diffInMinutes = $data && $data->jam_masuk && $data->jam_keluar
                ? Carbon::parse($data->jam_masuk)->diffInMinutes(Carbon::parse($data->jam_keluar))
                : null;

            $hours = is_int($diffInMinutes) ? (int) floor($diffInMinutes / 60) : 0;
            $minutes = is_int($diffInMinutes) ? (int) ($diffInMinutes % 60) : 0;

            $presensi[] = (object) [
                'id_presensi' => $data->id_presensi ?? null,
                'tanggal' => $tanggal,
                'hari' => $hari,
                'jam_masuk' => $data->jam_masuk ?? null,
                'lokasi_masuk' => $data->lokasi_masuk ?? null,
                'jam_keluar' => $data->jam_keluar ?? null,
                'lokasi_keluar' => $data->lokasi_keluar ?? null,
                'total_minutes' => $diffInMinutes,
                'total_jam_text' => is_int($diffInMinutes) && $diffInMinutes > 0 ? "{$hours} Jam {$minutes} Menit" : '-',
            ];
        }

        return Inertia::render('Admin/Presensi/ByUser', [
            'users' => $users->map(fn (User $user) => [
                'user_id' => $user->user_id,
                'nama' => $user->nama,
                'posisi' => $user->posisi,
                'role' => $user->role,
                'status' => $user->status,
            ])->values(),
            'selectedUser' => $selectedUser
                ? [
                    'user_id' => $selectedUser->user_id,
                    'nama' => $selectedUser->nama,
                    'posisi' => $selectedUser->posisi,
                    'role' => $selectedUser->role,
                    'status' => $selectedUser->status,
                ]
                : null,
            'presensi' => collect($presensi)->map(fn ($item) => [
                'id_presensi' => $item->id_presensi,
                'tanggal' => $item->tanggal,
                'hari' => $item->hari,
                'jam_masuk' => $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : null,
                'lokasi_masuk' => $item->lokasi_masuk,
                'jam_keluar' => $item->jam_keluar ? Carbon::parse($item->jam_keluar)->format('H:i') : null,
                'lokasi_keluar' => $item->lokasi_keluar,
                'total_minutes' => $item->total_minutes,
                'total_jam_text' => $item->total_jam_text,
            ])->values(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'userId' => $userId ? (int) $userId : null,
            'jumlahHari' => $jumlahHari,
            'izinDates' => $izinDates,
            'lemburDates' => $lemburDates,
        ]);
    }


    // 📌 3. Fitur Edit Presensi
    public function editPresensi($id)
    {
        session()->put('previous_url', url()->previous()); // Simpan URL sebelumnya
        $presensi = Presensi::findOrFail($id);

        return Inertia::render('Admin/Presensi/Edit', [
            'presensi' => [
                'id_presensi' => $presensi->id_presensi,
                'nama' => $presensi->user?->nama,
                'tanggal_human' => Carbon::parse($presensi->tanggal)->translatedFormat('d F Y'),
                'jam_masuk' => $presensi->jam_masuk ? Carbon::parse($presensi->jam_masuk)->format('H:i') : null,
                'lokasi_masuk' => $presensi->lokasi_masuk,
                'jam_keluar' => $presensi->jam_keluar ? Carbon::parse($presensi->jam_keluar)->format('H:i') : null,
                'lokasi_keluar' => $presensi->lokasi_keluar,
                'previous_url' => url()->previous(),
            ],
        ]);
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

    // 📌 4. Tambah Presensi Manual
    public function createManualPresensi()
    {
        $users = User::orderBy('nama')->get();
        $office = OfficeLocation::get();
        return Inertia::render('Admin/Presensi/Manual', [
            'users' => $users->map(fn (User $user) => [
                'user_id' => $user->user_id,
                'nama' => $user->nama,
                'posisi' => $user->posisi,
                'role' => $user->role,
                'status' => $user->status,
            ]),
            'officeLocation' => $office,
        ]);
    }

    public function storeManualPresensi(Request $request)
    {
        $operationType = (string) $request->input('operation_type', 'presensi');

        if (! in_array($operationType, ['presensi', 'izin', 'lembur'], true)) {
            return back()->with('error', 'Jenis input tidak valid.');
        }

        $timezone = (string) config('app.timezone');

        try {
            if ($operationType === 'presensi') {
                $validated = $request->validate([
                    'user_id' => ['required', 'exists:users,user_id'],
                    'tanggal' => ['required', 'date'],
                    'jam_masuk' => ['required', 'date_format:H:i'],
                    'jam_keluar' => ['required', 'date_format:H:i', 'after:jam_masuk'],
                    'lokasi_masuk' => ['nullable', 'string', 'max:255'],
                    'lokasi_keluar' => ['nullable', 'string', 'max:255'],
                    'latitude_masuk' => ['nullable', 'numeric', 'between:-90,90'],
                    'longitude_masuk' => ['nullable', 'numeric', 'between:-180,180'],
                    'accuracy_masuk' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                    'latitude_keluar' => ['nullable', 'numeric', 'between:-90,90'],
                    'longitude_keluar' => ['nullable', 'numeric', 'between:-180,180'],
                    'accuracy_keluar' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                ]);

                ManualOperationalInput::createPresensi([
                    'user_id' => (int) $validated['user_id'],
                    'tanggal' => (string) $validated['tanggal'],
                    'jam_masuk' => (string) $validated['jam_masuk'],
                    'jam_keluar' => (string) $validated['jam_keluar'],
                    'lokasi_masuk' => $validated['lokasi_masuk'] ?? null,
                    'lokasi_keluar' => $validated['lokasi_keluar'] ?? null,
                    'latitude_masuk' => array_key_exists('latitude_masuk', $validated) ? (is_numeric($validated['latitude_masuk']) ? (float) $validated['latitude_masuk'] : null) : null,
                    'longitude_masuk' => array_key_exists('longitude_masuk', $validated) ? (is_numeric($validated['longitude_masuk']) ? (float) $validated['longitude_masuk'] : null) : null,
                    'accuracy_masuk' => array_key_exists('accuracy_masuk', $validated) && is_numeric($validated['accuracy_masuk']) ? (int) round((float) $validated['accuracy_masuk']) : null,
                    'latitude_keluar' => array_key_exists('latitude_keluar', $validated) ? (is_numeric($validated['latitude_keluar']) ? (float) $validated['latitude_keluar'] : null) : null,
                    'longitude_keluar' => array_key_exists('longitude_keluar', $validated) ? (is_numeric($validated['longitude_keluar']) ? (float) $validated['longitude_keluar'] : null) : null,
                    'accuracy_keluar' => array_key_exists('accuracy_keluar', $validated) && is_numeric($validated['accuracy_keluar']) ? (int) round((float) $validated['accuracy_keluar']) : null,
                    'ip_masuk' => $request->ip(),
                    'ua_masuk' => (string) $request->userAgent(),
                    'ip_keluar' => $request->ip(),
                    'ua_keluar' => (string) $request->userAgent(),
                ], $timezone);

                return redirect()->route('admin.presensi.by-date')->with('success', 'Presensi manual berhasil ditambahkan!');
            }

            if ($operationType === 'izin') {
                $validated = $request->validate([
                    'user_id' => ['required', 'exists:users,user_id'],
                    'tanggal' => ['required', 'date'],
                    'keterangan' => ['nullable', 'string', 'max:255'],
                ]);

                ManualOperationalInput::createIzin([
                    'user_id' => (int) $validated['user_id'],
                    'tanggal' => (string) $validated['tanggal'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ], $timezone);

                return redirect()->route('admin.presensi.create')->with('success', 'Izin/cuti manual berhasil ditambahkan!');
            }

            // lembur
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,user_id'],
                'tanggal' => ['required', 'date'],
                'jam_mulai' => ['required', 'date_format:H:i'],
                'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
                'lokasi_mulai' => ['nullable', 'string', 'max:255'],
                'lokasi_selesai' => ['nullable', 'string', 'max:255'],
                'latitude_mulai' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude_mulai' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy_mulai' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'latitude_selesai' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude_selesai' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy_selesai' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            ]);

            ManualOperationalInput::createLembur([
                'user_id' => (int) $validated['user_id'],
                'tanggal' => (string) $validated['tanggal'],
                'jam_mulai' => (string) $validated['jam_mulai'],
                'jam_selesai' => (string) $validated['jam_selesai'],
                'lokasi_mulai' => $validated['lokasi_mulai'] ?? null,
                'lokasi_selesai' => $validated['lokasi_selesai'] ?? null,
                'latitude_mulai' => array_key_exists('latitude_mulai', $validated) ? (is_numeric($validated['latitude_mulai']) ? (float) $validated['latitude_mulai'] : null) : null,
                'longitude_mulai' => array_key_exists('longitude_mulai', $validated) ? (is_numeric($validated['longitude_mulai']) ? (float) $validated['longitude_mulai'] : null) : null,
                'accuracy_mulai' => array_key_exists('accuracy_mulai', $validated) && is_numeric($validated['accuracy_mulai']) ? (int) round((float) $validated['accuracy_mulai']) : null,
                'latitude_selesai' => array_key_exists('latitude_selesai', $validated) ? (is_numeric($validated['latitude_selesai']) ? (float) $validated['latitude_selesai'] : null) : null,
                'longitude_selesai' => array_key_exists('longitude_selesai', $validated) ? (is_numeric($validated['longitude_selesai']) ? (float) $validated['longitude_selesai'] : null) : null,
                'accuracy_selesai' => array_key_exists('accuracy_selesai', $validated) && is_numeric($validated['accuracy_selesai']) ? (int) round((float) $validated['accuracy_selesai']) : null,
                'ip_mulai' => $request->ip(),
                'ua_mulai' => (string) $request->userAgent(),
                'ip_selesai' => $request->ip(),
                'ua_selesai' => (string) $request->userAgent(),
            ], $timezone);

            return redirect()->route('admin.presensi.create')->with('success', 'Lembur manual berhasil ditambahkan!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // 📌 5. Rekap Presensi

    public function rekapPresensi(Request $request)
    {
        \Carbon\Carbon::setLocale('id');

        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $bulanInt = (int) $bulan;
        $tahunInt = (int) $tahun;
        $result = MonthlyPresensiRekap::build($tahunInt, $bulanInt);

        return Inertia::render('Admin/Presensi/Rekap', [
            'rekap' => $result['rekap'],
            'bulan' => $bulan,
            'tahun' => $tahun,
            'summary' => $result['summary'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        if ($request->filled('tanggal')) {
            $tanggal = $request->input('tanggal');

            $presensi = Presensi::whereDate('tanggal', $tanggal)->with('user')->get();
            $izinCount = Izin::whereDate('tanggal_izin', $tanggal)->count();
            $lemburCount = Lembur::whereDate('tanggal', $tanggal)->count();
            $belumCheckoutCount = $presensi->filter(fn (Presensi $item) => $item->jam_masuk && ! $item->jam_keluar)->count();

            $summary = [
                'total_hadir' => $presensi->count(),
                'total_belum_checkout' => $belumCheckoutCount,
                'total_izin_cuti' => $izinCount,
                'total_lembur' => $lemburCount,
            ];

            return Excel::download(new PresensiByDateExport($presensi, $tanggal, $summary), "presensi-{$tanggal}.xlsx");
        }

        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $bulanInt = (int) $bulan;
        $tahunInt = (int) $tahun;
        $result = MonthlyPresensiRekap::build($tahunInt, $bulanInt);

        $fileName = "rekap-presensi-{$bulan}-{$tahun}.xlsx";
        return Excel::download(new RekapPresensiExport($result['rekap'], $bulan, $tahun, $result['summary']), $fileName);
    }

    public function exportPDF(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? Carbon::today()->format('Y-m-d');

        $presensi = Presensi::whereDate('tanggal', $tanggal)->with('user')->get();

        $izinCount = Izin::whereDate('tanggal_izin', $tanggal)->count();
        $lemburByUser = Lembur::whereDate('tanggal', $tanggal)->get(['user_id'])->groupBy('user_id');
        $lemburCount = $lemburByUser->count();
        $belumCheckoutCount = $presensi->filter(fn (Presensi $item) => $item->jam_masuk && ! $item->jam_keluar)->count();

        $summary = [
            'total_hadir' => $presensi->count(),
            'total_belum_checkout' => $belumCheckoutCount,
            'total_izin_cuti' => $izinCount,
            'total_lembur' => $lemburCount,
        ];

        return FacadePdf::loadView('admin.export_pdf', [
            'tanggal' => $tanggal,
            'presensi' => $presensi,
            'summary' => $summary,
            'lemburUserIds' => $lemburByUser->keys()->values()->all(),
            'generatedAt' => Carbon::now(config('app.timezone'))->translatedFormat('d F Y H:i'),
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
        ])->download("presensi-{$tanggal}.pdf");
    }

    public function exportRekapPDF(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $bulanInt = (int) $bulan;
        $tahunInt = (int) $tahun;

        $result = MonthlyPresensiRekap::build($tahunInt, $bulanInt);

        $generatedAt = Carbon::now(config('app.timezone'))->translatedFormat('d F Y H:i');

        return FacadePdf::loadView('admin.export_rekap_presensi_pdf', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'rekap' => $result['rekap'],
            'summary' => $result['summary'],
            'generatedAt' => $generatedAt,
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
        ])->download("rekap-presensi-{$bulan}-{$tahun}.pdf");
    }

}
