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
use Illuminate\Validation\Rule;
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
use App\Services\Presensi\DailyOperationalRecapService;
use App\Services\Presensi\LemburUpdateService;
use App\Services\Presensi\UserMonthlyOperationalRecapService;

class AdminPresensiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Presensi/Index');
    }

    // 📌 1. Lihat Presensi Berdasarkan Tanggal
    public function presensiByDate(Request $request, DailyOperationalRecapService $dailyRecap)
    {
        $tanggal = $request->input('tanggal') ?? Carbon::today()->format('Y-m-d');
        $result = $dailyRecap->build($tanggal);

        return Inertia::render('Admin/Presensi/ByDate', [
            'tanggal' => $result['tanggal'],
            'presensi' => $result['rows'],
            'total' => $result['total'],
            'summary' => $result['summary'],
        ]);
    }

    // 📌 2. Lihat Presensi Berdasarkan Karyawan
    public function presensiByUser(Request $request, UserMonthlyOperationalRecapService $monthlyRecap)
    {
        $userId = $request->query('user_id');
        $bulan = (int) ($request->query('bulan') ?? now()->month);
        $tahun = (int) ($request->query('tahun') ?? now()->year);
        $periodType = $request->query('period_type') === 'range' ? 'range' : 'month';
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Ambil daftar karyawan untuk selector (urutkan agar mudah dicari)
        $users = User::orderBy('nama')->get();

        // Pastikan user_id valid
        if ($userId && !User::where('user_id', $userId)->exists()) {
            return redirect()->route('admin.presensi.by-user')->with('error', 'Karyawan tidak ditemukan.');
        }

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $periodLabel = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');

        if ($periodType === 'range') {
            if (! $startDate || ! $endDate) {
                return redirect()
                    ->route('admin.presensi.by-user', $request->except(['period_type', 'start_date', 'end_date']))
                    ->with('error', 'Tanggal mulai dan tanggal selesai wajib diisi untuk custom range.');
            }

            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();
            } catch (\Throwable) {
                return redirect()
                    ->route('admin.presensi.by-user', $request->except(['period_type', 'start_date', 'end_date']))
                    ->with('error', 'Format tanggal custom range tidak valid.');
            }

            if ($end->lessThan($start)) {
                return redirect()
                    ->route('admin.presensi.by-user', $request->except(['period_type', 'start_date', 'end_date']))
                    ->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
            }

            $jumlahHari = $start->diffInDays($end) + 1;

            if ($jumlahHari > 31) {
                return redirect()
                    ->route('admin.presensi.by-user', $request->except(['period_type', 'start_date', 'end_date']))
                    ->with('error', 'Custom range maksimal 31 hari.');
            }

            $startDate = $start->toDateString();
            $endDate = $end->toDateString();
            $periodLabel = $start->translatedFormat('d M Y').' - '.$end->translatedFormat('d M Y');
        }

        $selectedUser = $userId ? User::where('user_id', $userId)->first() : null;
        $emptySummary = [
            'total_hari_hadir' => 0,
            'total_hadir_lengkap' => 0,
            'total_belum_checkout' => 0,
            'total_izin_cuti' => 0,
            'total_lembur' => 0,
            'total_tidak_hadir' => 0,
            'total_jam_kerja_minutes' => 0,
            'total_jam_lembur_minutes' => 0,
        ];

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
                'summary' => $emptySummary,
                'periodType' => $periodType,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'periodLabel' => $periodLabel,
            ]);
        }

        $result = $periodType === 'range'
            ? $monthlyRecap->buildForRange($selectedUser, $startDate, $endDate)
            : $monthlyRecap->build($selectedUser, $bulan, $tahun);

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
            'presensi' => $result['rows'],
            'bulan' => $bulan,
            'tahun' => $tahun,
            'userId' => $userId ? (int) $userId : null,
            'jumlahHari' => $result['jumlah_hari'],
            'summary' => $result['summary'],
            'periodType' => $periodType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
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

    public function editLembur($id, LemburUpdateService $lemburUpdate)
    {
        $lembur = Lembur::with('user')->findOrFail($id);
        $metadata = $lemburUpdate->metadata();
        $tanggal = Carbon::parse($lembur->tanggal)->toDateString();

        return Inertia::render('Admin/Presensi/EditLembur', [
            'lembur' => [
                'id_lembur' => (int) $lembur->id_lembur,
                'nama' => $lembur->user?->nama,
                'tanggal' => $tanggal,
                'tanggal_human' => Carbon::parse($tanggal)->translatedFormat('d F Y'),
                'jam_mulai' => $lembur->jam_mulai_lembur ? Carbon::parse($lembur->getRawOriginal('jam_mulai_lembur') ?? $lembur->jam_mulai_lembur)->format('H:i') : null,
                'jam_selesai' => $lembur->jam_pulang_lembur ? Carbon::parse($lembur->getRawOriginal('jam_pulang_lembur') ?? $lembur->jam_pulang_lembur)->format('H:i') : null,
                'status' => $metadata['status_field'] ? ($lembur->{$metadata['status_field']} ?? null) : null,
                'note' => $metadata['note_field'] ? ($lembur->{$metadata['note_field']} ?? null) : null,
                'status_field' => $metadata['status_field'],
                'status_options' => $metadata['status_options'],
                'note_field' => $metadata['note_field'],
                'back_url' => route('admin.presensi.by-date', ['tanggal' => $tanggal]),
            ],
        ]);
    }

    public function updateLembur(Request $request, $id, LemburUpdateService $lemburUpdate)
    {
        $lembur = Lembur::findOrFail($id);
        $metadata = $lemburUpdate->metadata();

        $rules = [
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:jam_mulai'],
        ];

        if ($metadata['status_field'] && $metadata['status_options'] !== []) {
            $rules['status'] = ['nullable', Rule::in($metadata['status_options'])];
        }

        if ($metadata['note_field']) {
            $rules['note'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);
        $lemburUpdate->update($lembur, $validated);

        $tanggal = Carbon::parse($lembur->fresh()->tanggal)->toDateString();

        return redirect()
            ->route('admin.presensi.by-date', ['tanggal' => $tanggal])
            ->with('success', 'Lembur berhasil diperbarui!');
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

                return redirect()->route('admin.presensi.create')->with('success', 'Presensi manual berhasil ditambahkan.');
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

                return redirect()->route('admin.presensi.create')->with('success', 'Izin/Cuti manual berhasil ditambahkan.');
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

            return redirect()->route('admin.presensi.create')->with('success', 'Lembur manual berhasil ditambahkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // 📌 5. Rekap Presensi

    public function rekapPresensi(Request $request)
    {
        \Carbon\Carbon::setLocale('id');

        $period = $this->resolveRekapPeriod($request);

        if (isset($period['redirect'])) {
            return $period['redirect'];
        }

        $includeInactive = $request->boolean('include_inactive');
        $result = $period['period_type'] === 'range'
            ? MonthlyPresensiRekap::buildForRange($period['start_date'], $period['end_date'], $includeInactive)
            : MonthlyPresensiRekap::build((int) $period['tahun'], (int) $period['bulan'], $includeInactive);

        return Inertia::render('Admin/Presensi/Rekap', [
            'rekap' => $result['rekap'],
            'bulan' => $period['bulan'],
            'tahun' => $period['tahun'],
            'summary' => $result['summary'],
            'periodType' => $period['period_type'],
            'startDate' => $period['start_date'],
            'endDate' => $period['end_date'],
            'periodLabel' => $period['period_label'],
            'reportTitle' => $period['report_title'],
            'includeInactive' => $includeInactive,
        ]);
    }

    public function exportExcel(Request $request, DailyOperationalRecapService $dailyRecap)
    {
        if ($request->filled('tanggal')) {
            $tanggal = $request->input('tanggal');
            $result = $dailyRecap->build($tanggal);

            return Excel::download(new PresensiByDateExport(collect($result['rows']), $result['tanggal'], $result['summary']), "presensi-{$result['tanggal']}.xlsx");
        }

        $period = $this->resolveRekapPeriod($request);

        if (isset($period['redirect'])) {
            return $period['redirect'];
        }

        $includeInactive = $request->boolean('include_inactive');
        $result = $period['period_type'] === 'range'
            ? MonthlyPresensiRekap::buildForRange($period['start_date'], $period['end_date'], $includeInactive)
            : MonthlyPresensiRekap::build((int) $period['tahun'], (int) $period['bulan'], $includeInactive);

        $fileName = $period['period_type'] === 'range'
            ? "rekap-presensi-{$period['start_date']}-{$period['end_date']}.xlsx"
            : "rekap-presensi-{$period['bulan']}-{$period['tahun']}.xlsx";

        return Excel::download(new RekapPresensiExport($result['rekap'], $period['bulan'], $period['tahun'], $result['summary'], [
            ...$period,
            'include_inactive' => $includeInactive,
        ]), $fileName);
    }

    public function exportPDF(Request $request, DailyOperationalRecapService $dailyRecap)
    {
        $tanggal = $request->input('tanggal') ?? Carbon::today()->format('Y-m-d');
        $result = $dailyRecap->build($tanggal);

        return FacadePdf::loadView('admin.export_pdf', [
            'tanggal' => $result['tanggal'],
            'presensi' => $result['rows'],
            'summary' => $result['summary'],
            'generatedAt' => Carbon::now(config('app.timezone'))->translatedFormat('d F Y H:i'),
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
        ])->download("presensi-{$result['tanggal']}.pdf");
    }

    public function exportRekapPDF(Request $request)
    {
        $period = $this->resolveRekapPeriod($request);

        if (isset($period['redirect'])) {
            return $period['redirect'];
        }

        $includeInactive = $request->boolean('include_inactive');
        $result = $period['period_type'] === 'range'
            ? MonthlyPresensiRekap::buildForRange($period['start_date'], $period['end_date'], $includeInactive)
            : MonthlyPresensiRekap::build((int) $period['tahun'], (int) $period['bulan'], $includeInactive);

        $generatedAt = Carbon::now(config('app.timezone'))->translatedFormat('d F Y H:i');

        return FacadePdf::loadView('admin.export_rekap_presensi_pdf', [
            'bulan' => $period['bulan'],
            'tahun' => $period['tahun'],
            'rekap' => $result['rekap'],
            'summary' => $result['summary'],
            'period' => [
                ...$period,
                'include_inactive' => $includeInactive,
            ],
            'generatedAt' => $generatedAt,
            'timezoneLabel' => 'Asia/Jakarta (WIB)',
        ])->download($period['period_type'] === 'range'
            ? "rekap-presensi-{$period['start_date']}-{$period['end_date']}.pdf"
            : "rekap-presensi-{$period['bulan']}-{$period['tahun']}.pdf");
    }

    private function resolveRekapPeriod(Request $request): array
    {
        $periodType = $request->query('period_type') === 'range' ? 'range' : 'month';
        $bulan = str_pad((string) $request->input('bulan', date('m')), 2, '0', STR_PAD_LEFT);
        $tahun = (string) $request->input('tahun', date('Y'));
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($periodType === 'range') {
            if (! $startDate || ! $endDate) {
                return [
                    'redirect' => redirect()
                        ->route('admin.presensi.rekap.presensi', $request->except(['period_type', 'start_date', 'end_date']))
                        ->with('error', 'Tanggal mulai dan tanggal selesai wajib diisi untuk custom range.'),
                ];
            }

            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();
            } catch (\Throwable) {
                return [
                    'redirect' => redirect()
                        ->route('admin.presensi.rekap.presensi', $request->except(['period_type', 'start_date', 'end_date']))
                        ->with('error', 'Format tanggal custom range tidak valid.'),
                ];
            }

            if ($end->lessThan($start)) {
                return [
                    'redirect' => redirect()
                        ->route('admin.presensi.rekap.presensi', $request->except(['period_type', 'start_date', 'end_date']))
                        ->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'),
                ];
            }

            $daysInPeriod = (int) $start->diffInDays($end) + 1;

            if ($daysInPeriod > 31) {
                return [
                    'redirect' => redirect()
                        ->route('admin.presensi.rekap.presensi', $request->except(['period_type', 'start_date', 'end_date']))
                        ->with('error', 'Custom range maksimal 31 hari.'),
                ];
            }

            return [
                'period_type' => 'range',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'period_label' => $start->translatedFormat('d M Y').' - '.$end->translatedFormat('d M Y'),
                'report_title' => 'Rekap Presensi Periode',
                'days_in_period' => $daysInPeriod,
            ];
        }

        $monthDate = Carbon::create((int) $tahun, (int) $bulan, 1);

        return [
            'period_type' => 'month',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'start_date' => $monthDate->toDateString(),
            'end_date' => $monthDate->copy()->endOfMonth()->toDateString(),
            'period_label' => $monthDate->translatedFormat('F Y'),
            'report_title' => 'Rekap Presensi Bulanan',
            'days_in_period' => $monthDate->daysInMonth,
        ];
    }

}
