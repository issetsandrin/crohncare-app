<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoJob;
use App\Models\Exame;
use Illuminate\Console\Command;

class EnviarLembretesExames extends Command
{
    protected $signature = 'lembretes:exames';
    protected $description = 'Envia notificações push para exames agendados para amanhã';

    public function handle()
    {
        $amanha = now()->addDay();
        $inicio = $amanha->copy()->startOfDay();
        $fim    = $amanha->copy()->endOfDay();

        $exames = Exame::where('status', 'agendado')
            ->whereBetween('data', [$inicio, $fim])
            ->get();

        foreach ($exames as $exame) {
            $hora  = $exame->data->format('H:i');
            $title = "🔬 {$exame->nome} amanhã";
            $body  = "Seu exame de {$exame->nome} está agendado para amanhã às {$hora}";

            if ($exame->local) {
                $body .= " em {$exame->local}";
            }

            EnviarNotificacaoJob::dispatch($exame->user_id, $title, $body);
        }

        $this->info("Jobs de exames enfileirados: {$exames->count()}");
        return 0;
    }
}
