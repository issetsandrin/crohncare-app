<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestarNotificacao extends Command
{
    protected $signature = 'lembretes:testar';
    protected $description = 'Envia uma notificação de teste para todos os dispositivos';

    public function handle()
    {
        $tokens = DeviceToken::pluck('token');

        if ($tokens->isEmpty()) {
            $this->error('Nenhum dispositivo registrado. Ative as notificações no app primeiro.');
            return 1;
        }

        $this->info("Enviando para {$tokens->count()} dispositivo(s)...");

        foreach ($tokens as $token) {
            try {
                $accessToken = $this->getAccessToken();
                $projectId = config('services.firebase.project_id');

                $response = Http::withToken($accessToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $token,
                            'data' => [
                                'title' => 'Teste CrohnCare!',
                                'body' => 'Se você recebeu isso, as notificações estão funcionando!',
                            ],
                            'webpush' => [
                                'headers' => [
                                    'Urgency' => 'high',
                                    'TTL' => '86400',
                                ],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $this->info('Notificação enviada com sucesso!');
                } else {
                    $this->error('Erro: ' . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Erro: {$e->getMessage()}");
            }
        }

        return 0;
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
