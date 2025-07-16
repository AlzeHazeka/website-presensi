<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\AkunDisetujuiNotification;
use App\Notifications\AkunDitolakNotification;


class RegistrasiController extends Controller
{
    public function index()
    {
        $users = User::where('status', 'menunggu')->orderBy('created_at', 'desc')->get();
        return view('registrasi.antrian', compact('users'));
    }

    public function approve($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $user->status = 'aktif';
        $user->save();
        $user->notify(new AkunDisetujuiNotification());

        return redirect()->back()->with('success', 'Registrasi Karyawan berhasil diterima!');
    }

    public function reject($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $user->status = 'ditolak'; // status enum 'ditolak'
        $user->save();

        $user->notify(new AkunDitolakNotification());

        // Soft delete (agar tidak muncul di query umum, tapi masih bisa diakses jika dibutuhkan)
        $user->delete();

        return redirect()->back()->with('success', 'Registrasi karyawan ditolak.');
    }
}
