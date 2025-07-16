<?php

namespace App\Livewire\AdminEdit;

use Livewire\Component;
use App\Models\User;

class UpdateProfileInformationForm extends Component
{
    public $user;
    public $state = [];

    protected $rules = [
        'state.nama' => 'required|string|max:255',
        'state.username' => 'required|string|max:255|unique:users,username,' . '$user->id',
        'state.email' => 'required|email|unique:users,email,' . '$user->id',
        'state.alamat' => 'nullable|string|max:255',
        'state.telepon' => 'nullable|string|max:15',
        'state.tanggal_lahir' => 'nullable|date',
        'state.posisi' => 'nullable|string|max:255',
        'state.gaji' => 'nullable|numeric',
        'state.tipe_gaji' => 'nullable|string|in:harian,bulanan',
        'state.status' => 'nullable|string|in:aktif,tidak aktif',
        'state.role' => 'nullable|string|in:Admin,HR,Karyawan',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->state = $user->toArray();
    }

    public function update()
    {
        // Perbaiki aturan validasi untuk menggunakan $this->user->id
        $this->validate([
            'state.nama' => 'required|string|max:255',
            'state.username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'state.email' => 'required|email|unique:users,email,' . $this->user->id,
            'state.alamat' => 'nullable|string|max:255',
            'state.telepon' => 'nullable|string|max:15',
            'state.tanggal_lahir' => 'nullable|date',
            'state.posisi' => 'nullable|string|max:255',
            'state.gaji' => 'nullable|numeric',
            'state.tipe_gaji' => 'nullable|string|in:harian,bulanan',
            'state.status' => 'nullable|string|in:aktif,tidak aktif',
            'state.role' => 'nullable|string|in:Admin,HR,Karyawan',
        ]);

        // Perbarui data pengguna
        $this->user->update($this->state);

        // Tampilkan pesan sukses
        session()->flash('message', 'Profil berhasil diperbarui!');
        return redirect()->route('users.edit', $this->user);
    }

    public function render()
    {
        return view('livewire.admin-edit.update-profile-information-form');
    }
}
