<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Medicamento;
use App\Models\Diario;
use App\Models\Crise;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nome' => 'Paciente Teste',
            'email' => 'teste@crohncare.com',
            'password' => 'password',
        ]);

        // Mesalazina 800mg - 08h e 20h - estoque ok (28 dias)
        $mesalazina = Medicamento::create([
            'nome' => 'Mesalazina',
            'dose' => '800mg',
            'instrucoes' => 'Tomar com alimento. Não mastigar o comprimido.',
            'periodicidade_tipo' => 'diario',
            'periodicidade_valor' => null,
            'ativo' => true,
        ]);
        $mesalazina->horarios()->createMany([
            ['horario' => '08:00'],
            ['horario' => '20:00'],
        ]);
        $mesalazina->estoque()->create([
            'quantidade_atual' => 56,
            'dose_diaria' => 2,
            'data_ultimo_reabastecimento' => '2026-03-01',
        ]);

        // Azatioprina 50mg - 08h - estoque urgente (2 dias)
        $azatioprina = Medicamento::create([
            'nome' => 'Azatioprina',
            'dose' => '50mg',
            'instrucoes' => 'Tomar em jejum. Monitorar hemograma regularmente.',
            'periodicidade_tipo' => 'dias_semana',
            'periodicidade_valor' => ['dias' => [1, 3, 5]],
            'ativo' => true,
        ]);
        $azatioprina->horarios()->create(['horario' => '08:00']);
        $azatioprina->estoque()->create([
            'quantidade_atual' => 2,
            'dose_diaria' => 1,
            'data_ultimo_reabastecimento' => '2026-02-15',
        ]);

        // Diários
        Diario::create([
            'data' => '2026-02-20',
            'sintomas' => 'Leve desconforto abdominal após almoço',
            'intensidade' => 2,
            'observacoes' => 'Comi fritura no almoço, possível gatilho.',
        ]);

        Diario::create([
            'data' => '2026-03-05',
            'sintomas' => 'Diarreia pela manhã, cólicas leves',
            'intensidade' => 3,
            'observacoes' => 'Período de estresse no trabalho.',
        ]);

        Diario::create([
            'data' => '2026-03-15',
            'sintomas' => 'Dia bom, sem sintomas relevantes',
            'intensidade' => 1,
            'observacoes' => null,
        ]);

        // Crise
        Crise::create([
            'data_hora' => '2026-03-17 14:30',
            'sintomas' => 'Dor abdominal intensa, diarreia com sangue',
            'intensidade' => 4,
            'duracao_estimada' => '3 horas',
            'observacoes' => 'Precisei sair do trabalho. Tomei buscopan e melhorou parcialmente.',
        ]);
    }
}
