<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Menampilkan daftar user
    public function index()
    {
        $users = User::paginate(20); // Menampilkan 10 user per halaman
        return view('data-user.index', compact('users'));
    }

    // Menampilkan form untuk mengedit user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('data-user.edit', compact('user'));
    }

    // Menyimpan perubahan data user
    public function update(Request $request, $id)
    {
         // Validasi data yang dikirimkan melalui form
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id .',user_id',
            'username' => 'required|string|max:20',
            'alamat' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:15',
            'posisi' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_masuk' => 'nullable|date',
            'gaji' => 'nullable|integer',
            'tipe_gaji' => 'required|in:harian,bulanan',
            'status' => 'required|in:aktif,tidak aktif',
            'role' => 'required|in:Admin,HR,Karyawan',
        ]);
    $user = User::findOrFail($id);
    $user->update($request->all());
        return redirect()->route('data-user.index')->with('success', 'User berhasil diperbarui');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('data-user.index', $id)->with('success', 'Password berhasil diperbarui dan Anda tetap login.');
    }

    // Menghapus user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // Menghapus user berdasarkan ID
        return redirect()->route('data-user.index')->with('success', 'User berhasil dihapus');
    }
}
