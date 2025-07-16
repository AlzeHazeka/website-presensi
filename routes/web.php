<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\Admin\AdminPresensiController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\RegistrasiController;

// Rute untuk halaman utama
Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware(['guest'])
    ->name('register');

Route::get('/register/waiting', function () {
    return view('auth.waiting');
})->name('register.waiting');

// Rute yang dilindungi oleh middleware
Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::prefix('data-user')->group(function () {
        // Menampilkan daftar user
        Route::get('users', [UserController::class, 'index'])->name('data-user.index');

        // Menampilkan form untuk edit user
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('data-user.edit');

        // Menyimpan perubahan data user
        Route::put('users/{id}', [UserController::class, 'update'])->name('data-user.update');

        Route::put('/data-user/{id}/password', [UserController::class, 'updatePassword'])->name('update-password');

        // Menghapus user
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('data-user.destroy');
    });


    // **Rute Presensi untuk User**
    Route::prefix('presensi')->group(function () {
        Route::get('/', [PresensiController::class, 'index'])->name('presensi.index');
        Route::post('/masuk', [PresensiController::class, 'presensiMasuk'])->name('presensi.masuk');
        Route::post('/keluar', [PresensiController::class, 'presensiKeluar'])->name('presensi.keluar');
        Route::get('/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
        Route::get('/events', [PresensiController::class, 'getEvents'])->name('presensi.events');
        Route::get('/detail', [PresensiController::class, 'getDetail'])->name('presensi.detail');
        Route::get('/hitung-gaji', [PresensiController::class, 'hitungGaji'])->name('presensi.hitungGaji');
        Route::get('/status', [PresensiController::class, 'getPresensiStatus'])->name('presensi.status');
    });

    Route::get('/lembur', [LemburController::class, 'index'])->name('lembur.index');
    Route::post('/lembur/mulai', [LemburController::class, 'mulaiLembur'])->name('lembur.mulai');
    Route::post('/lembur/pulang', [LemburController::class, 'pulangLembur'])->name('lembur.pulang');

    Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
    Route::post('/izin/ajukan', [IzinController::class, 'ajukan'])->name('izin.ajukan');

    // **Rute Presensi untuk Admin**
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/presensi', [AdminPresensiController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/by-date', [AdminPresensiController::class, 'presensiByDate'])->name('presensi.by-date');
        Route::get('/presensi/by-user', [AdminPresensiController::class, 'presensiByUser'])->name('presensi.by-user');


        Route::get('/presensi/edit/{id}', [AdminPresensiController::class, 'editPresensi'])->name('presensi.edit');
        Route::post('/presensi/update/{id}', [AdminPresensiController::class, 'updatePresensi'])->name('presensi.update');
        Route::get('/lembur/edit/{id}', [AdminPresensiController::class, 'editLembur'])->name('lembur.edit');
        Route::post('/lembur/update/{id}', [AdminPresensiController::class, 'updateLembur'])->name('lembur.update');


        Route::get('/presensi/create-manual', [AdminPresensiController::class, 'createManualPresensi'])->name('presensi.create');
        Route::post('/presensi/store-manual', [AdminPresensiController::class, 'storeManualPresensi'])->name('presensi.store');
        Route::get('/presensi/rekap-presensi', [AdminPresensiController::class, 'rekapPresensi'])->name('presensi.rekap.presensi');
        // Penghapusan Data
        Route::delete('presensi/delete/{id}', [AdminPresensiController::class, 'destroy'])
        ->name('presensi.destroy');
        Route::delete('lembur/delete/{id}', [AdminPresensiController::class, 'destroyLembur'])
        ->name('lembur.destroy');
        Route::delete('izin/delete/{id}', [AdminPresensiController::class, 'destroyIzin'])
        ->name('izin.destroy');

        //Export
        Route::get('/presensi/rekap/export', [AdminPresensiController::class, 'exportExcel'])->name('presensi.rekap.export');
        Route::get('/presensi/by-user/export', [AdminPresensiController::class, 'exportByUserExcel'])->name('byuser.export');
        Route::get('/presensi/by-date/export', [AdminPresensiController::class, 'exportByDateExcel'])->name('presensi.bydate.export');

        Route::get('/antrian-registrasi', [RegistrasiController::class, 'index'])->name('registrasi.antrian');
        Route::post('/antrian-registrasi/{id}/acc', [RegistrasiController::class, 'approve'])->name('registrasi.acc');
        Route::post('/antrian-registrasi/{id}/tolak', [RegistrasiController::class, 'reject'])->name('registrasi.tolak');




    });

    Route::get('/admin/presensi/export/pdf', [AdminPresensiController::class, 'exportPDF'])->name('admin.presensi.export.pdf');
    Route::get('/admin/presensi/export/excel', [AdminPresensiController::class, 'exportExcel'])->name('admin.presensi.export.excel');


    // Cek data user (Debugging)
    Route::get('/cek-user', function () {
        dd(auth()->user());
    })->middleware('auth');
});
