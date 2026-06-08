<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\RoleCatalog;
use App\Support\RoleSync;
use App\Support\Permissions;
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
        if (! $user->can(Permissions::PROFILE_MANAGE)) abort(403);

        // Validasi input
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:100'],
            'posisi' => ['nullable', 'string', 'max:50'],
            'telepon' => ['nullable', 'string', 'max:15'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_masuk' => ['nullable', 'date'],
            'gaji' => ['nullable', 'integer'],
            'tipe_gaji' => ['nullable', 'in:harian,bulanan'],
            'status' => ['nullable', 'in:aktif,tidak aktif'],
            'role' => ['nullable', Rule::in(RoleCatalog::availableRoleNames())],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        // Hanya tambahkan aturan keunikan jika email baru berbeda
        if (($input['email'] ?? null) !== $user->email) {
            $rules['email'][] = Rule::unique('users')->ignore($user->getKey(), $user->getKeyName());
        }

        Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if (($input['email'] ?? null) !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'nama' => $input['nama'],
                'username' => $input['username'],
                'email' => $input['email'],
                'alamat' => $input['alamat'] ?? null,
                'posisi' => $input['posisi'] ?? null,
                'telepon' => $input['telepon'] ?? null,
                'tanggal_lahir'=> $input['tanggal_lahir'] ?? null,
                'tanggal_masuk' => $input['tanggal_masuk'] ?? null,
                'gaji' => $input['gaji'] ?? null,
                'tipe_gaji' => $input['tipe_gaji'] ?? null,
                'status' => $input['status'] ?? null,
                'role' => $input['role'] ?? null,
            ])->save();
        }

        RoleSync::sync($user, $input['role'] ?? $user->role);
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
            'alamat' => $input['alamat'] ?? null,
            'posisi' => $input['posisi'] ?? null,
            'telepon' => $input['telepon'] ?? null,
            'tanggal_lahir'=> $input['tanggal_lahir'] ?? null,
            'tanggal_masuk' => $input['tanggal_masuk'] ?? null,
            'gaji' => $input['gaji'] ?? null,
            'tipe_gaji' => $input['tipe_gaji'] ?? null,
            'status' => $input['status'] ?? null,
            'role' => $input['role'] ?? null,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
