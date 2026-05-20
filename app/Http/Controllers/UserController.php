<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Support\RoleSync;

class UserController extends Controller
{
    // Menampilkan daftar user
    public function index(Request $request)
    {
        $role = trim((string) $request->query('role', ''));
        $status = trim((string) $request->query('status', ''));
        $q = trim((string) $request->query('q', ''));

        $query = User::query();
        if ($role !== '' && in_array($role, config('app.user_roles', []), true)) {
            $query->where('role', $role);
        }

        if ($status !== '' && in_array($status, ['aktif', 'tidak aktif'], true)) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('nama', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('nama')->paginate(20)->withQueryString();
        return Inertia::render('DataUser/Index', [
            'users' => $users,
            'filters' => [
                'role' => $role !== '' ? $role : null,
                'status' => $status !== '' ? $status : null,
                'q' => $q !== '' ? $q : null,
            ],
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('DataUser/Show', [
            'user' => $user,
        ]);
    }

    // Menampilkan form untuk membuat user
    public function create()
    {
        $authUser = request()->user();
        if (! $authUser || ! $authUser->can(Permissions::USERS_CREATE)) abort(403);

        return Inertia::render('DataUser/Create', [
            'defaultRole' => 'Karyawan',
            'defaultStatus' => 'aktif',
        ]);
    }

    // Menyimpan user baru
    public function store(Request $request)
    {
        $authUser = $request->user();
        if (! $authUser || ! $authUser->can(Permissions::USERS_CREATE)) abort(403);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:20|unique:users,username',
            'alamat' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:15',
            'posisi' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_masuk' => 'nullable|date',
            'gaji' => 'nullable|integer',
            'tipe_gaji' => 'required|in:harian,bulanan',
            'status' => 'required|in:aktif,tidak aktif',
            'role' => ['required', Rule::in(config('app.user_roles'))],
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'alamat' => $request->input('alamat'),
            'telepon' => $request->input('telepon'),
            'posisi' => $request->input('posisi'),
            'tanggal_lahir' => $request->input('tanggal_lahir'),
            'tanggal_masuk' => $request->input('tanggal_masuk'),
            'gaji' => $request->input('gaji'),
            'tipe_gaji' => $request->input('tipe_gaji'),
            'status' => $request->input('status'),
            'role' => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        RoleSync::sync($user, $request->input('role'));

        return redirect()->route('data-user.index')->with('success', 'User berhasil dibuat');
    }

    // Menampilkan form untuk mengedit user
    public function edit($id)
    {
        $authUser = request()->user();
        if (! $authUser || ! $authUser->can(Permissions::USERS_EDIT)) abort(403);

        $user = User::findOrFail($id);
        return Inertia::render('DataUser/Edit', [
            'user' => $user,
        ]);
    }

    // Menyimpan perubahan data user
    public function update(Request $request, $id)
    {
        $authUser = $request->user();
        if (! $authUser || ! $authUser->can(Permissions::USERS_EDIT)) abort(403);

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
            'role' => ['required', Rule::in(config('app.user_roles'))],
        ]);

        $user = User::findOrFail($id);
        $user->update($request->all());
        RoleSync::sync($user, $request->input('role'));

        return redirect()->route('data-user.index')->with('success', 'User berhasil diperbarui');
    }

    public function updatePassword(Request $request, $id)
    {
        $authUser = $request->user();
        if (! $authUser || ! $authUser->can(Permissions::PASSWORD_RESET)) abort(403);

        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('data-user.edit', $id)->with('success', 'Password berhasil diperbarui dan Anda tetap login.');
    }

    // Menghapus user
    public function destroy($id)
    {
        $authUser = request()->user();
        if (! $authUser || ! $authUser->can(Permissions::USERS_DELETE)) abort(403);

        $user = User::findOrFail($id);
        $user->delete(); // Menghapus user berdasarkan ID
        return redirect()->route('data-user.index')->with('success', 'User berhasil dihapus');
    }
}
