<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $fillable = ['user_id', 'nome', 'dose', 'instrucoes', 'periodicidade_tipo', 'periodicidade_valor', 'ativo', 'exige_comprovacao'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'ativo'               => 'boolean',
        'exige_comprovacao'   => 'boolean',
        'periodicidade_valor' => 'array',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function estoque()
    {
        return $this->hasOne(Estoque::class);
    }

    public function tomadas()
    {
        return $this->hasMany(Tomada::class);
    }

    public function getDiasRestantesAttribute()
    {
        if (!$this->estoque || $this->estoque->dose_diaria === 0) {
            return 0;
        }
        return (int) floor($this->estoque->quantidade_atual / $this->estoque->dose_diaria);
    }
}
