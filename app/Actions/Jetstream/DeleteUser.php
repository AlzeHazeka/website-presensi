<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Support\Permissions;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        if (! $user->can(Permissions::SYSTEM_MANAGE)) abort(403);

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
