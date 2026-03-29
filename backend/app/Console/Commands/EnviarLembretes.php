<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoJob;
use App\Models\User;
use Illuminate\Console\Command;

class EnviarLembretes extends Command
{
    protected $signature = 'lembretes:enviar';
    protected $description = 'Envia notificações push para remédios próximos';

    public function handle()
    {
        $agora  = now();
        $em5min = $agora->copy()->addMinutes(5);

        $horaAtual = $agora->format('H:i');
        $hora5min  = $em5min->format('H:i');

        $users = User::whereHas('medicamentos', function ($q) {
            $q->where('ativo', true)->where('periodicidade_tipo', '!=', 'sob_demanda');
        })->with(['medicamentos' => function ($q) {
            $q->where('ativo', true)->where('periodicidade_tipo', '!=', 'sob_demanda')->with('horarios');
        }])->get();

        $totalJobs = 0;

        foreach ($users as $user) {
            foreach ($user->medicamentos as $med) {

                // — Verificação de rotatividade para intervalo —
                if ($med->periodicidade_tipo === 'intervalo') {
                    $intervalo = $med->periodicidade_valor['intervalo'] ?? 2;
                    $diasDecorridos = (int) $med->created_at->startOfDay()->diffInDays(now()->startOfDay());

                    if (in_array($intervalo, [2, 3])) {
                        // Ciclo de 5 dias (2+3): dose nos dias 0 e $intervalo de cada ciclo
                        $outro = 5 - $intervalo;
                        $posicaoNoCiclo = $diasDecorridos % 5;

                        if (!in_array($posicaoNoCiclo, [0, $intervalo])) {
                            continue; // Hoje não é dia de dose
                        }
                    } else {
                        // Outros intervalos: verifica a cada N dias exatos
                        if ($diasDecorridos % $intervalo !== 0) {
                            continue;
                        }
                        $outro = null;
                    }
                }

                foreach ($med->horarios as $horario) {
                    $h = substr($horario->horario, 0, 5);

                    $doseHoje   = $med->dose_hoje;
                    $infoExtra  = '';

                    // Info de ciclo
                    if ($med->periodicidade_tipo === 'ciclo') {
                        $ciclo = $med->periodicidade_valor['ciclo'] ?? [];
                        $diasDecorridos = $med->created_at->startOfDay()->diffInDays(now()->startOfDay());
                        $diaCiclo  = ($diasDecorridos % count($ciclo)) + 1;
                        $totalDias = count($ciclo);
                        $infoExtra = " (Dia {$diaCiclo} de {$totalDias} do ciclo)";
                    }

                    // Info de rotatividade 2-3
                    if ($med->periodicidade_tipo === 'intervalo') {
                        $intervalo = $med->periodicidade_valor['intervalo'] ?? 2;
                        if (in_array($intervalo, [2, 3])) {
                            $outro = 5 - $intervalo;
                            $diasDecorridos = (int) $med->created_at->startOfDay()->diffInDays(now()->startOfDay());
                            $posicaoNoCiclo = $diasDecorridos % 5;
                            $proximoIntervalo = ($posicaoNoCiclo === 0) ? $intervalo : $outro;
                            $infoExtra = " (próxima dose em {$proximoIntervalo} dias)";
                        }
                    }

                    $dadosMed = [
                        'medicamento_id'    => (string) $med->id,
                        'medicamento_nome'  => $med->nome,
                        'exige_comprovacao' => $med->exige_comprovacao ? 'true' : 'false',
                    ];

                    if ($h === $hora5min) {
                        EnviarNotificacaoJob::dispatch(
                            $user->id,
                            "Faltam 5 minutos para o {$med->nome}",
                            "Prepare-se para tomar {$doseHoje} às {$h}{$infoExtra}",
                            $dadosMed,
                        );
                        $totalJobs++;
                    }

                    if ($h === $horaAtual) {
                        EnviarNotificacaoJob::dispatch(
                            $user->id,
                            "Hora do seu remédio!",
                            "Está na hora de tomar {$med->nome} – {$doseHoje}{$infoExtra}",
                            $dadosMed,
                        );
                        $totalJobs++;
                    }
                }
            }
        }

        $this->info("Jobs de medicamentos enfileirados: {$totalJobs}");
        return 0;
    }
}
