<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\DeviceToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestarNotificacao extends Command
{
    protected $signature = 'lembretes:testar';
    protected $description = 'Envia uma notificação de teste para todos os dispositivos';

    public function handle()
    {
        $deviceTokens = DeviceToken::all();

        if ($deviceTokens->isEmpty()) {
            $this->error('Nenhum dispositivo registrado. Ative as notificações no app primeiro.');
            return 1;
        }

        $this->info("Enviando para {$deviceTokens->count()} dispositivo(s)...");

        $title = 'Teste CrohnCare!';
        $body = 'Se você recebeu isso, as notificações estão funcionando!';

        $usuariosNotificados = [];

        foreach ($deviceTokens as $deviceToken) {
            try {
                $accessToken = $this->getAccessToken();
                $projectId = config('services.firebase.project_id');

                $response = Http::withToken($accessToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $deviceToken->token,
                            'data' => [
                                'title' => $title,
                                'body' => $body,
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

                    if (!in_array($deviceToken->user_id, $usuariosNotificados)) {
                        Aviso::create([
                            'user_id' => $deviceToken->user_id,
                            'titulo' => $title,
                            'mensagem' => $body,
                        ]);
                        $usuariosNotificados[] = $deviceToken->user_id;
                    }
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
