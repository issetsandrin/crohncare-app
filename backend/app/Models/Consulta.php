<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $fillable = ['user_id', 'medico', 'especialidade', 'data_hora', 'local', 'observacoes', 'status'];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
