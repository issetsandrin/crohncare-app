<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilCrohn extends Model
{
    protected $table = 'perfil_crohn';

    protected $fillable = [
        'user_id',
        'tipo_doenca',
        'ano_diagnostico',
        'localizacao',
        'situacao_atual',
        'tem_acompanhamento',
        'frequencia_consultas',
        'objetivos',
    ];

    protected $casts = [
        'tem_acompanhamento' => 'boolean',
        'objetivos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
