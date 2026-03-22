<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crise extends Model
{
    protected $fillable = ['user_id', 'data_hora', 'sintomas', 'intensidade', 'duracao_estimada', 'observacoes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'data_hora' => 'datetime',
        'intensidade' => 'integer',
    ];
}
