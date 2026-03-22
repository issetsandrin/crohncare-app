<?php

namespace App\Http\Controllers;

use App\Models\PerfilCrohn;
use Illuminate\Http\Request;

class PerfilCrohnController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_doenca' => 'nullable|in:crohn,retocolite,indeterminada,nao_sei',
            'ano_diagnostico' => 'nullable|integer|min:1950|max:' . date('Y'),
            'localizacao' => 'nullable|in:ileal,colonico,ileocolonico,trato_superior,nao_sei',
            'situacao_atual' => 'nullable|in:remissao,crise_leve,crise_moderada,crise_grave,recente',
            'tem_acompanhamento' => 'nullable|boolean',
            'frequencia_consultas' => 'nullable|string|max:100',
            'objetivos' => 'nullable|array',
            'objetivos.*' => 'string|max:100',
        ]);

        $user = $request->user();

        $perfil = PerfilCrohn::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $user->update(['onboarding_completo' => true]);

        return response()->json($perfil, 201);
    }

    public function show(Request $request)
    {
        $perfil = $request->user()->perfilCrohn;

        return response()->json($perfil);
    }
}
