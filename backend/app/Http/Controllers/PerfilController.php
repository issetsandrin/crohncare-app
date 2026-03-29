<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class PerfilController extends Controller
{
    public function stats()
    {
        $user = auth()->user();
        $hoje = Carbon::today();

        // Medicamentos ativos
        $medsAtivos = $user->medicamentos()->where('ativo', true)->count();

        // Total de registros no diário
        $totalDiarios = $user->diarios()->count();

        // Sequência de dias consecutivos com registro no diário
        $streak = 0;
        $dia = $hoje->copy();
        while ($user->diarios()->whereDate('data', $dia)->exists()) {
            $streak++;
            $dia->subDay();
        }

        // Total de crises registradas
        $totalCrises = $user->crises()->count();

        // Dias desde a última crise
        $ultimaCrise = $user->crises()->orderBy('data_hora', 'desc')->first();
        $diasSemCrise = $ultimaCrise
            ? (int) $hoje->diffInDays(Carbon::parse($ultimaCrise->data_hora)->startOfDay(), true)
            : null;

        // Próxima consulta
        $proximaConsulta = $user->consultas()
            ->where('data_hora', '>=', now())
            ->where('status', 'agendada')
            ->orderBy('data_hora')
            ->first();

        // Total de consultas realizadas
        $consultasRealizadas = $user->consultas()->where('status', 'realizada')->count();

        // Membro desde
        $membroDesde = $user->created_at->format('d/m/Y');
        $diasMembro = $hoje->diffInDays($user->created_at);

        return response()->json([
            'meds_ativos' => $medsAtivos,
            'total_diarios' => $totalDiarios,
            'streak_diario' => $streak,
            'total_crises' => $totalCrises,
            'dias_sem_crise' => $diasSemCrise,
            'consultas_realizadas' => $consultasRealizadas,
            'proxima_consulta' => $proximaConsulta ? [
                'medico' => $proximaConsulta->medico,
                'especialidade' => $proximaConsulta->especialidade,
                'data_hora' => $proximaConsulta->data_hora->format('Y-m-d H:i'),
            ] : null,
            'membro_desde' => $membroDesde,
            'dias_membro' => $diasMembro,
        ]);
    }
}
