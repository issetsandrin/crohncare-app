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
                foreach ($med->horarios as $horario) {
                    $h = substr($horario->horario, 0, 5);

                    $doseHoje = $med->dose_hoje;

                    $isCiclo = $med->periodicidade_tipo === 'ciclo';
                    $infoCiclo = '';
                    if ($isCiclo) {
                        $ciclo = $med->periodicidade_valor['ciclo'] ?? [];
                        $diasDecorridos = $med->created_at->startOfDay()->diffInDays(now()->startOfDay());
                        $diaCiclo = ($diasDecorridos % count($ciclo)) + 1;
                        $totalDias = count($ciclo);
                        $infoCiclo = " (Dia {$diaCiclo} de {$totalDias} do ciclo)";
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
                            "Prepare-se para tomar {$doseHoje} às {$h}{$infoCiclo}",
                            $dadosMed,
                        );
                        $totalJobs++;
                    }

                    if ($h === $horaAtual) {
                        EnviarNotificacaoJob::dispatch(
                            $user->id,
                            "Hora do seu remédio!",
                            "Está no horário de tomar o seu remédio: {$med->nome} - {$doseHoje}{$infoCiclo}",
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
