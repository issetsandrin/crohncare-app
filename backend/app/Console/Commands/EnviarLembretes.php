<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EnviarLembretes extends Command
{
    protected $signature = 'lembretes:enviar';
    protected $description = 'Envia notificações push para remédios próximos';

    public function handle()
    {
        $agora = now();
        $em5min = $agora->copy()->addMinutes(5);

        $horaAtual = $agora->format('H:i');
        $hora5min = $em5min->format('H:i');

        $users = User::whereHas('medicamentos', function ($q) {
            $q->where('ativo', true)->where('periodicidade_tipo', '!=', 'sob_demanda');
        })->with(['medicamentos' => function ($q) {
            $q->where('ativo', true)->where('periodicidade_tipo', '!=', 'sob_demanda')->with('horarios');
        }])->get();

        $totalEnviados = 0;

        foreach ($users as $user) {
            $notificacoes = [];

            foreach ($user->medicamentos as $med) {
                foreach ($med->horarios as $horario) {
                    $h = substr($horario->horario, 0, 5);

                    if ($h === $hora5min) {
                        $notificacoes[] = [
                            'title' => "{$med->nome} em 5 minutos",
                            'body' => "{$med->dose} — {$h}",
                        ];
                    }

                    if ($h === $horaAtual) {
                        $notificacoes[] = [
                            'title' => "Hora do {$med->nome}!",
                            'body' => "Tomar {$med->dose} agora — {$h}",
                        ];
                    }
                }
            }

            if (empty($notificacoes)) {
                continue;
            }

            $tokens = DeviceToken::where('user_id', $user->id)->pluck('token');

            foreach ($notificacoes as $notif) {
                foreach ($tokens as $token) {
                    $this->enviarFcm($token, $notif['title'], $notif['body']);
                    $totalEnviados++;
                }
            }
        }

        $this->info("Lembretes enviados: {$totalEnviados}");
        return 0;
    }

    private function enviarFcm(string $token, string $title, string $body): void
    {
        try {
            $accessToken = $this->getAccessToken();
            $projectId = config('services.firebase.project_id');

            Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'webpush' => [
                            'notification' => [
                                'icon' => '/icons/icon-192x192.svg',
                                'vibrate' => [200, 100, 200],
                                'requireInteraction' => true,
                            ],
                        ],
                    ],
                ]);
        } catch (\Exception $e) {
            $this->error("Erro FCM: {$e->getMessage()}");

            if (str_contains($e->getMessage(), 'NOT_FOUND') || str_contains($e->getMessage(), 'UNREGISTERED')) {
                DeviceToken::where('token', $token)->delete();
            }
        }
    }

    private function getAccessToken(): string
    {
        $credentialsPath = config('services.firebase.credentials');
        $client = new \Google\Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $token = $client->getAccessToken();

        return $token['access_token'];
    }
}
