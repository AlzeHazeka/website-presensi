<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        // Validasi input
    $rules = [
        'nama' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:20'],
        'email' => ['required', 'email', 'max:255'],
        'alamat' => ['nullable', 'string', 'max:100'],
        'posisi' => ['nullable', 'string', 'max:50'],
        'tanggal_masuk' => ['nullable', 'date'],
        'gaji' => ['nullable', 'integer'],
        'tipe_gaji' => ['nullable', 'in:harian,bulanan'],
        'status' => ['nullable', 'in:aktif,tidak aktif'],
        'role' => ['nullable', 'in:Admin,HR,Karyawan'],
    ];

    // Hanya tambahkan aturan keunikan jika email baru berbeda
    if ($input['email'] !== $user->email) {
        $rules['email'][] = Rule::unique('users');
    }

    Validator::make($input, $rules, [
        'email.unique' => 'Username sudah digunakan oleh pengguna lain.'
    ])->validate();

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'nama' => $input['nama'],
                'username' => $input['username'],
                'email' => $input['email'],
                'alamat' => $input['alamat'],
                'posisi' => $input['posisi'],
                'telepon' => $input['telepon'],
                'tanggal_lahir'=> $input['tanggal_lahir'],
                'tanggal_masuk' => $input['tanggal_masuk'],
                'gaji' => $input['gaji'],
                'tipe_gaji' => $input['tipe_gaji'],
                'status' => $input['status'],
                'role' => $input['role'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'nama' => $input['nama'],
            'username' => $input['username'],
            'email' => $input['email'],
            'alamat' => $input['alamat'],
            'posisi' => $input['posisi'],
            'telepon' => $input['telepon'],
            'tanggal_lahir'=> $input['tanggal_lahir'],
            'tanggal_masuk' => $input['tanggal_masuk'],
            'gaji' => $input['gaji'],
            'tipe_gaji' => $input['tipe_gaji'],
            'status' => $input['status'],
            'role' => $input['role'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
