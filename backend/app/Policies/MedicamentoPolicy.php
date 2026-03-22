<?php

namespace App\Policies;

use App\Models\Medicamento;
use App\Models\User;

class MedicamentoPolicy
{
    public function view(User $user, Medicamento $medicamento): bool
    {
        return $user->id === $medicamento->user_id;
    }

    public function update(User $user, Medicamento $medicamento): bool
    {
        return $user->id === $medicamento->user_id;
    }

    public function delete(User $user, Medicamento $medicamento): bool
    {
        return $user->id === $medicamento->user_id;
    }
}
