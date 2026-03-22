<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerificarEstoque extends Command
{
    protected $signature = 'estoque:verificar';
    protected $description = 'Envia alerta quando o estoque de um medicamento está baixo (≤ 3 dias)';

    public function handle()
    {
        $users = User::whereHas('medicamentos', function ($q) {
            $q->where('ativo', true)->whereHas('estoque');
        })->with(['medicamentos' => function ($q) {
            $q->where('ativo', true)->with('estoque');
        }])->get();

        $totalEnviados = 0;

        foreach ($users as $user) {
            $tokens = DeviceToken::where('user_id', $user->id)->pluck('token');

            if ($tokens->isEmpty()) {
                continue;
            }

            foreach ($user->medicamentos as $med) {
                if (!$med->estoque || $med->estoque->dose_diaria === 0) {
                    continue;
                }

                $diasRestantes = (int) floor($med->estoque->quantidade_atual / $med->estoque->dose_diaria);

                if ($diasRestantes > 3) {
                    continue;
                }

                $title = "Estoque baixo: {$med->nome}";
                $body = $diasRestantes === 0
                    ? "Estoque esgotado! Reabasteça o quanto antes."
                    : "Restam apenas {$diasRestantes} dia(s) de estoque. Reabasteça em breve.";

                Aviso::create([
                    'user_id' => $user->id,
                    'titulo' => $title,
                    'mensagem' => $body,
                ]);

                foreach ($tokens as $token) {
                    $this->enviarFcm($token, $title, $body);
                    $totalEnviados++;
                }
            }
        }

        $this->info("Alertas de estoque enviados: {$totalEnviados}");
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
