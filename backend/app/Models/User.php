<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['nome', 'email', 'password', 'onboarding_completo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }

    public function diarios()
    {
        return $this->hasMany(Diario::class);
    }

    public function crises()
    {
        return $this->hasMany(Crise::class);
    }

    public function avisos()
    {
        return $this->hasMany(Aviso::class);
    }

    public function perfilCrohn()
    {
        return $this->hasOne(PerfilCrohn::class);
    }
}
