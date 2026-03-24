<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoJob;
use App\Models\User;
use Illuminate\Console\Command;

class VerificarEstoque extends Command
{
    protected $signature = 'estoque:verificar';
    protected $description = 'Envia alerta quando o estoque de um medicamento está baixo (≤ 3 dias)';

    public function handle()
    {
        $users = User::whereHas('medicamentos', function ($q) {
            $q->where('ativo', true)->whereHas('estoque');
        })->with(['medicamentos' => function ($q) {
            $q->where('ativo', true)->with('estoque');
        }])->get();

        $totalJobs = 0;

        foreach ($users as $user) {
            foreach ($user->medicamentos as $med) {
                if (!$med->estoque || $med->estoque->dose_diaria === 0) {
                    continue;
                }

                $diasRestantes = (int) floor($med->estoque->quantidade_atual / $med->estoque->dose_diaria);

                if ($diasRestantes > 3) {
                    continue;
                }

                $title = "Estoque baixo: {$med->nome}";
                $body  = $diasRestantes === 0
                    ? "Estoque esgotado! Reabasteça o quanto antes."
                    : "Restam apenas {$diasRestantes} dia(s) de estoque. Reabasteça em breve.";

                EnviarNotificacaoJob::dispatch($user->id, $title, $body);
                $totalJobs++;
            }
        }

        $this->info("Jobs de estoque enfileirados: {$totalJobs}");
        return 0;
    }
}
