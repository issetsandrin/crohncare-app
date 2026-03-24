<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\DeviceToken;
use App\Models\Exame;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
            ->with('user')
            ->get();

        $totalEnviados = 0;

        foreach ($exames as $exame) {
            $hora   = $exame->data->format('H:i');
            $title  = "🔬 {$exame->nome} amanhã";
            $body   = "Seu exame de {$exame->nome} está agendado para amanhã às {$hora}";

            if ($exame->local) {
                $body .= " em {$exame->local}";
            }

            Aviso::create([
                'user_id'  => $exame->user_id,
                'titulo'   => $title,
                'mensagem' => $body,
            ]);

            $tokens = DeviceToken::where('user_id', $exame->user_id)->pluck('token');

            foreach ($tokens as $token) {
                $this->enviarFcm($token, $title, $body);
                $totalEnviados++;
            }
        }

        $this->info("Lembretes de exames enviados: {$totalEnviados}");
        return 0;
    }

    private function enviarFcm(string $token, string $title, string $body): void
    {
        try {
            $accessToken = $this->getAccessToken();
            $projectId   = config('services.firebase.project_id');

            Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'data'  => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'webpush' => [
                            'headers' => [
                                'Urgency' => 'high',
                                'TTL'     => '86400',
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
