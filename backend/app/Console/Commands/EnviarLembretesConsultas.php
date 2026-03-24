<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoJob;
use App\Models\Consulta;
use Illuminate\Console\Command;

class EnviarLembretesConsultas extends Command
{
    protected $signature = 'lembretes:consultas';
    protected $description = 'Envia notificações push para consultas agendadas para amanhã';

    public function handle()
    {
        $amanha = now()->addDay();
        $inicio = $amanha->copy()->startOfDay();
        $fim    = $amanha->copy()->endOfDay();

        $consultas = Consulta::where('status', 'agendada')
            ->whereBetween('data_hora', [$inicio, $fim])
            ->get();

        foreach ($consultas as $consulta) {
            $hora  = $consulta->data_hora->format('H:i');
            $title = "🩺 Consulta com {$consulta->medico} amanhã";
            $body  = "Sua consulta com {$consulta->medico} ({$consulta->especialidade}) está agendada para amanhã às {$hora}";

            if ($consulta->local) {
                $body .= " em {$consulta->local}";
            }

            EnviarNotificacaoJob::dispatch($consulta->user_id, $title, $body);
        }

        $this->info("Jobs de consultas enfileirados: {$consultas->count()}");
        return 0;
    }
}
