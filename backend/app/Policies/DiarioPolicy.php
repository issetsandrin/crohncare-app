<?php

namespace App\Policies;

use App\Models\Diario;
use App\Models\User;

class DiarioPolicy
{
    public function update(User $user, Diario $diario): bool
    {
        return $user->id === $diario->user_id;
    }

    public function delete(User $user, Diario $diario): bool
    {
        return $user->id === $diario->user_id;
    }
}
