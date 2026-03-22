<?php

namespace App\Policies;

use App\Models\Crise;
use App\Models\User;

class CrisePolicy
{
    public function update(User $user, Crise $crise): bool
    {
        return $user->id === $crise->user_id;
    }

    public function delete(User $user, Crise $crise): bool
    {
        return $user->id === $crise->user_id;
    }
}
