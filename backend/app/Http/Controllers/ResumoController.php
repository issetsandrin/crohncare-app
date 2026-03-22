<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicamentoResource;
use App\Http\Resources\CriseResource;
use App\Http\Resources\DiarioResource;
use Carbon\Carbon;

class ResumoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $medicamentos = $user->medicamentos()->with(['horarios', 'estoque'])->where('ativo', true)->get();

        $hoje = Carbon::today();
        $ultimosDiarios = $user->diarios()
            ->where('data', '>=', $hoje->copy()->subDays(7))
            ->orderBy('data', 'desc')
            ->take(3)
            ->get();

        $ultimasCrises = $user->crises()
            ->where('data_hora', '>=', $hoje->copy()->subDays(30))
            ->orderBy('data_hora', 'desc')
            ->take(3)
            ->get();

        $alertasEstoque = $medicamentos->filter(fn ($m) => $m->dias_restantes <= 7);

        return response()->json([
            'medicamentos' => MedicamentoResource::collection($medicamentos),
            'alertas_estoque' => MedicamentoResource::collection($alertasEstoque),
            'ultimos_diarios' => DiarioResource::collection($ultimosDiarios),
            'ultimas_crises' => CriseResource::collection($ultimasCrises),
            'total_crises_mes' => $user->crises()
                ->whereMonth('data_hora', $hoje->month)
                ->whereYear('data_hora', $hoje->year)
                ->count(),
        ]);
    }
}
