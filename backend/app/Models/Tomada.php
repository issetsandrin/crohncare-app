<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tomada extends Model
{
    protected $fillable = ['user_id', 'medicamento_id', 'foto_path', 'tomado_em'];

    protected $casts = [
        'tomado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
