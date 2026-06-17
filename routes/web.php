<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\Admin\AdminPresensiController;
use App\Http\Controllers\Payroll\DailyPayrollController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\LemburController;
use Inertia\Inertia;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureAnyPermission;
use App\Http\Middleware\EnsureAnyRole;
use App\Support\Permissions;
use App\Support\Roles;

// Rute untuk halaman utama
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Arsip landing lama (jangan dipakai lagi, tapi tidak dihapus)
Route::view('/welcome-legacy', 'welcome')->name('welcome.legacy');

// Rute yang dilindungi oleh middleware
Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


    Route::prefix('data-user')->middleware(EnsurePermission::class.':'.Permissions::USERS_VIEW)->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('data-user.index');
        Route::get('users/{id}', [UserController::class, 'show'])->name('data-user.show');

        Route::get('users/create', [UserController::class, 'create'])
            ->middleware(EnsurePermission::class.':'.Permissions::USERS_CREATE)
            ->name('data-user.create');
        Route::post('users', [UserController::class, 'store'])
            ->middleware(EnsurePermission::class.':'.Permissions::USERS_CREATE)
            ->name('data-user.store');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])
            ->middleware(EnsurePermission::class.':'.Permissions::USERS_EDIT)
            ->name('data-user.edit');
        Route::put('users/{id}', [UserController::class, 'update'])
            ->middleware(EnsurePermission::class.':'.Permissions::USERS_EDIT)
            ->name('data-user.update');
        Route::put('/data-user/{id}/password', [UserController::class, 'updatePassword'])
            ->middleware(EnsurePermission::class.':'.Permissions::PASSWORD_RESET)
            ->name('update-password');
        Route::delete('users/{id}', [UserController::class, 'destroy'])
            ->middleware(EnsurePermission::class.':'.Permissions::USERS_DELETE)
            ->name('data-user.destroy');
    });


    // **Rute Presensi untuk User**
    Route::prefix('presensi')->group(function () {
        Route::get('/', [PresensiController::class, 'index'])->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_VIEW)->name('presensi.index');
        Route::post('/masuk', [PresensiController::class, 'presensiMasuk'])->middleware(['throttle:presensi', EnsurePermission::class.':'.Permissions::PRESENSI_CREATE])->name('presensi.masuk');
        Route::post('/keluar', [PresensiController::class, 'presensiKeluar'])->middleware(['throttle:presensi', EnsurePermission::class.':'.Permissions::PRESENSI_CREATE])->name('presensi.keluar');
        Route::get('/riwayat', [PresensiController::class, 'riwayat'])->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_VIEW)->name('presensi.riwayat');
        Route::get('/events', [PresensiController::class, 'getEvents'])->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_VIEW)->name('presensi.events');
        Route::get('/detail', [PresensiController::class, 'getDetail'])->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_VIEW)->name('presensi.detail');
        Route::get('/hitung-gaji', [PresensiController::class, 'hitungGaji'])->middleware(EnsurePermission::class.':'.Permissions::PAYROLL_VIEW)->name('presensi.hitungGaji');
        Route::get('/status', [PresensiController::class, 'getPresensiStatus'])->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_VIEW)->name('presensi.status');
    });

    // **Rute Izin & Lembur untuk User**
    Route::get('/izin', [IzinController::class, 'index'])->middleware(EnsurePermission::class.':'.Permissions::IZIN_VIEW)->name('izin.index');
    Route::post('/izin/ajukan', [IzinController::class, 'ajukan'])->middleware(EnsurePermission::class.':'.Permissions::IZIN_CREATE)->name('izin.ajukan');
    Route::get('/izin/eligibility', [IzinController::class, 'eligibility'])->middleware(EnsurePermission::class.':'.Permissions::IZIN_VIEW)->name('izin.eligibility');

    Route::get('/lembur', [LemburController::class, 'index'])->middleware(EnsurePermission::class.':'.Permissions::LEMBUR_VIEW)->name('lembur.index');
    Route::post('/lembur/mulai', [LemburController::class, 'mulaiLembur'])->middleware(EnsurePermission::class.':'.Permissions::LEMBUR_CREATE)->name('lembur.mulai');
    Route::post('/lembur/pulang', [LemburController::class, 'pulangLembur'])->middleware(EnsurePermission::class.':'.Permissions::LEMBUR_CREATE)->name('lembur.pulang');

    Route::prefix('payroll')->name('payroll.')->middleware([
        EnsurePermission::class.':'.Permissions::PAYROLL_VIEW,
        EnsureAnyRole::class.':'.implode('|', Roles::adminRoles()),
    ])->group(function () {
        Route::get('/daily', [DailyPayrollController::class, 'index'])->name('daily.index');
        Route::get('/daily/calculate', fn () => redirect()
            ->route('payroll.daily.index')
            ->with('warning', 'Halaman hitung gaji telah di-reset. Silakan pilih karyawan dan periode kembali.'))
            ->name('daily.calculate.reset');
        Route::post('/daily/calculate', [DailyPayrollController::class, 'calculate'])->name('daily.calculate');
        Route::post('/daily/print', [DailyPayrollController::class, 'print'])->name('daily.print');
    });

    // **Rute Presensi untuk Admin**
    Route::prefix('admin')->name('admin.')->middleware(EnsureAnyPermission::class.':'.Permissions::REPORT_DAILY_VIEW.'|'.Permissions::REPORT_BY_USER_VIEW.'|'.Permissions::REPORT_MONTHLY_VIEW)->group(function () {
        Route::get('/presensi', [AdminPresensiController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/by-date', [AdminPresensiController::class, 'presensiByDate'])
            ->middleware(EnsurePermission::class.':'.Permissions::REPORT_DAILY_VIEW)
            ->name('presensi.by-date');
        Route::get('/presensi/by-user', [AdminPresensiController::class, 'presensiByUser'])
            ->middleware(EnsurePermission::class.':'.Permissions::REPORT_BY_USER_VIEW)
            ->name('presensi.by-user');
        Route::get('/presensi/rekap-presensi', [AdminPresensiController::class, 'rekapPresensi'])
            ->middleware(EnsurePermission::class.':'.Permissions::REPORT_MONTHLY_VIEW)
            ->name('presensi.rekap.presensi');

        Route::get('/presensi/edit/{id}', [AdminPresensiController::class, 'editPresensi'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_EDIT)
            ->name('presensi.edit');
        Route::post('/presensi/update/{id}', [AdminPresensiController::class, 'updatePresensi'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_EDIT)
            ->name('presensi.update');
        Route::get('/presensi/lembur/{id}/edit', [AdminPresensiController::class, 'editLembur'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_EDIT)
            ->name('presensi.lembur.edit');
        Route::post('/presensi/lembur/{id}', [AdminPresensiController::class, 'updateLembur'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_EDIT)
            ->name('presensi.lembur.update');

        Route::get('/presensi/create-manual', [AdminPresensiController::class, 'createManualPresensi'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_MANUAL)
            ->name('presensi.create');
        Route::post('/presensi/store-manual', [AdminPresensiController::class, 'storeManualPresensi'])
            ->middleware(EnsurePermission::class.':'.Permissions::PRESENSI_MANUAL)
            ->name('presensi.store');

        Route::get('/presensi/rekap/export', [AdminPresensiController::class, 'exportExcel'])
            ->middleware(EnsurePermission::class.':'.Permissions::REPORT_EXPORT_EXCEL)
            ->name('presensi.rekap.export');
        Route::get('/presensi/rekap/pdf', [AdminPresensiController::class, 'exportRekapPDF'])
            ->middleware(EnsurePermission::class.':'.Permissions::REPORT_EXPORT_PDF)
            ->name('presensi.rekap.pdf');
    });

    Route::get('/admin/presensi/export/pdf', [AdminPresensiController::class, 'exportPDF'])
        ->middleware(EnsurePermission::class.':'.Permissions::REPORT_EXPORT_PDF)
        ->name('admin.presensi.export.pdf');
    Route::get('/admin/presensi/export/excel', [AdminPresensiController::class, 'exportExcel'])
        ->middleware(EnsurePermission::class.':'.Permissions::REPORT_EXPORT_EXCEL)
        ->name('admin.presensi.export.excel');


    // Cek data user (Debugging) - hanya untuk local/dev
    if (app()->environment('local')) {
        Route::get('/cek-user', function () {
            dd(auth()->user());
        })->middleware('auth');
    }
});
