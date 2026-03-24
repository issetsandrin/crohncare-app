<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroUso extends Model
{
    protected $table = 'registros_uso';

    protected $fillable = ['user_id', 'medicamento_id', 'foto_path', 'usado_em'];

    protected $casts = [
        'usado_em' => 'datetime',
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
