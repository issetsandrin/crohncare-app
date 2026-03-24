<?php

namespace App\Jobs;

use App\Models\Aviso;
use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class EnviarNotificacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly string $titulo,
        public readonly string $mensagem,
    ) {}

    public function handle(): void
    {
        Aviso::create([
            'user_id'  => $this->userId,
            'titulo'   => $this->titulo,
            'mensagem' => $this->mensagem,
        ]);

        $tokens = DeviceToken::where('user_id', $this->userId)->pluck('token');

        foreach ($tokens as $token) {
            $this->enviarFcm($token);
        }
    }

    private function enviarFcm(string $token): void
    {
        $accessToken = $this->getAccessToken();
        $projectId   = config('services.firebase.project_id');

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'data'  => [
                        'title' => $this->titulo,
                        'body'  => $this->mensagem,
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high',
                            'TTL'     => '86400',
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            $error = $response->json('error.status', '');
            if (in_array($error, ['NOT_FOUND', 'UNREGISTERED'])) {
                DeviceToken::where('token', $token)->delete();
                return;
            }
            throw new \RuntimeException("FCM error: {$error}");
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
