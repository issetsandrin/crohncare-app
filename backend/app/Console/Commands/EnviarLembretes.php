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

                    $dadosMed = [
                        'medicamento_id'    => (string) $med->id,
                        'medicamento_nome'  => $med->nome,
                        'exige_comprovacao' => $med->exige_comprovacao ? 'true' : 'false',
                    ];

                    if ($h === $hora5min) {
                        EnviarNotificacaoJob::dispatch(
                            $user->id,
                            "Lembrete: {$med->nome}",
                            "Tomar {$med->dose} às {$h} (em 5 minutos)",
                            $dadosMed,
                        );
                        $totalJobs++;
                    }

                    if ($h === $horaAtual) {
                        EnviarNotificacaoJob::dispatch(
                            $user->id,
                            "Hora de tomar {$med->nome}!",
                            "Tome {$med->dose} agora — {$h}",
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
