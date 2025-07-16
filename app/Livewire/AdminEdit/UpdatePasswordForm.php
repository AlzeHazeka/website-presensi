<?php

namespace App\Livewire\AdminEdit;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UpdatePasswordForm extends Component
{
    use PasswordValidationRules;

    public User $user; // Tambahkan properti ini

    public function mount(User $user) // Tambahkan parameter ini
    {
        $this->user = $user; // Simpan pengguna yang diteruskan
    }

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */

    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }

    public function render()
    {
        return view('livewire.admin-edit.update-password-form');
    }
}
