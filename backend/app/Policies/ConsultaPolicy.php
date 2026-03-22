<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

class ConsultaPolicy
{
    public function update(User $user, Consulta $consulta): bool
    {
        return $user->id === $consulta->user_id;
    }

    public function delete(User $user, Consulta $consulta): bool
    {
        return $user->id === $consulta->user_id;
    }
}
