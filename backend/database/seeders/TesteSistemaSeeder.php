<?php

namespace Database\Seeders;

use App\Models\Aviso;
use App\Models\Consulta;
use App\Models\Crise;
use App\Models\Diario;
use App\Models\Estoque;
use App\Models\Exame;
use App\Models\Horario;
use App\Models\Medicamento;
use App\Models\PerfilCrohn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TesteSistemaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuário ──────────────────────────────────────────────
        $user = User::updateOrCreate(
            ['email' => 'teste@crohncare.com'],
            [
                'nome'                => 'Teste Sistema',
                'password'            => Hash::make('teste123'),
                'onboarding_completo' => true,
            ]
        );

        // ── Perfil Crohn ─────────────────────────────────────────
        PerfilCrohn::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tipo_doenca'          => 'crohn',
                'ano_diagnostico'      => 2019,
                'localizacao'          => 'ileocolonico',
                'situacao_atual'       => 'crise_leve',
                'tem_acompanhamento'   => true,
                'frequencia_consultas' => 'trimestral',
                'objetivos'            => ['lembretes', 'sintomas', 'crises', 'consultas'],
            ]
        );

        // ── Medicamentos ─────────────────────────────────────────
        $medicamentos = [
            [
                'nome'              => 'Mesalazina',
                'dose'              => '800mg',
                'instrucoes'        => 'Tomar com água durante as refeições. Não mastigar.',
                'periodicidade_tipo' => 'diario',
                'horarios'          => ['08:00', '14:00', '20:00'],
                'estoque'           => ['quantidade_atual' => 45, 'dose_diaria' => 3],
            ],
            [
                'nome'              => 'Azatioprina',
                'dose'              => '100mg',
                'instrucoes'        => 'Tomar pela manhã junto ao café. Evitar sol excessivo.',
                'periodicidade_tipo' => 'diario',
                'horarios'          => ['08:00'],
                'estoque'           => ['quantidade_atual' => 8, 'dose_diaria' => 1],
            ],
            [
                'nome'              => 'Prednisona',
                'dose'              => '20mg',
                'instrucoes'        => 'Tomar com alimento. Não interromper abruptamente.',
                'periodicidade_tipo' => 'diario',
                'horarios'          => ['07:00'],
                'estoque'           => ['quantidade_atual' => 3, 'dose_diaria' => 1],
            ],
            [
                'nome'              => 'Omeprazol',
                'dose'              => '20mg',
                'instrucoes'        => 'Tomar 30 minutos antes do café da manhã.',
                'periodicidade_tipo' => 'diario',
                'horarios'          => ['07:30'],
                'estoque'           => ['quantidade_atual' => 28, 'dose_diaria' => 1],
            ],
            [
                'nome'              => 'Colecalciferol',
                'dose'              => '2000 UI',
                'instrucoes'        => 'Vitamina D. Tomar com refeição que contenha gordura.',
                'periodicidade_tipo' => 'diario',
                'horarios'          => ['12:00'],
                'estoque'           => ['quantidade_atual' => 60, 'dose_diaria' => 1],
            ],
        ];

        foreach ($medicamentos as $dados) {
            $horarios = $dados['horarios'];
            $estoqueData = $dados['estoque'];
            unset($dados['horarios'], $dados['estoque']);

            $med = Medicamento::firstOrCreate(
                ['user_id' => $user->id, 'nome' => $dados['nome']],
                array_merge($dados, ['user_id' => $user->id, 'ativo' => true])
            );

            foreach ($horarios as $h) {
                Horario::firstOrCreate(['medicamento_id' => $med->id, 'horario' => $h]);
            }

            Estoque::updateOrCreate(
                ['medicamento_id' => $med->id],
                array_merge($estoqueData, ['data_ultimo_reabastecimento' => now()->subDays(rand(5, 30))->toDateString()])
            );
        }

        // ── Crises (últimos 8 meses) ─────────────────────────────
        $sintomasOpcoes = [
            'dor abdominal', 'diarreia', 'náusea', 'fadiga', 'febre baixa',
            'sangramento', 'distensão', 'cólica', 'vômito', 'perda de apetite',
        ];

        $crises = [
            // Mês atual
            ['dias_atras' => 3,  'intensidade' => 3, 'duracao' => '2 horas',   'sintomas' => ['dor abdominal', 'diarreia', 'cólica']],
            ['dias_atras' => 9,  'intensidade' => 2, 'duracao' => '1 hora',    'sintomas' => ['diarreia', 'fadiga']],
            ['dias_atras' => 15, 'intensidade' => 4, 'duracao' => '4 horas',   'sintomas' => ['dor abdominal', 'vômito', 'febre baixa', 'fadiga']],
            // Mês -1
            ['dias_atras' => 35, 'intensidade' => 2, 'duracao' => '45 minutos','sintomas' => ['diarreia', 'cólica']],
            ['dias_atras' => 42, 'intensidade' => 3, 'duracao' => '3 horas',   'sintomas' => ['dor abdominal', 'náusea', 'distensão']],
            ['dias_atras' => 51, 'intensidade' => 5, 'duracao' => '6 horas',   'sintomas' => ['dor abdominal', 'vômito', 'febre baixa', 'sangramento', 'fadiga']],
            // Mês -2
            ['dias_atras' => 65, 'intensidade' => 1, 'duracao' => '30 minutos','sintomas' => ['diarreia']],
            ['dias_atras' => 78, 'intensidade' => 3, 'duracao' => '2 horas',   'sintomas' => ['dor abdominal', 'cólica', 'fadiga']],
            // Mês -3
            ['dias_atras' => 95, 'intensidade' => 2, 'duracao' => '1 hora',    'sintomas' => ['diarreia', 'perda de apetite']],
            ['dias_atras' => 110,'intensidade' => 4, 'duracao' => '5 horas',   'sintomas' => ['dor abdominal', 'vômito', 'febre baixa', 'distensão']],
            ['dias_atras' => 118,'intensidade' => 3, 'duracao' => '2 horas',   'sintomas' => ['diarreia', 'cólica', 'náusea']],
            // Mês -4
            ['dias_atras' => 130,'intensidade' => 1, 'duracao' => '20 minutos','sintomas' => ['diarreia']],
            ['dias_atras' => 145,'intensidade' => 2, 'duracao' => '1 hora',    'sintomas' => ['fadiga', 'perda de apetite']],
            // Mês -5
            ['dias_atras' => 160,'intensidade' => 3, 'duracao' => '3 horas',   'sintomas' => ['dor abdominal', 'náusea', 'cólica']],
            ['dias_atras' => 175,'intensidade' => 4, 'duracao' => '4 horas',   'sintomas' => ['dor abdominal', 'vômito', 'febre baixa']],
            // Mês -6
            ['dias_atras' => 195,'intensidade' => 2, 'duracao' => '1 hora',    'sintomas' => ['diarreia', 'fadiga']],
            ['dias_atras' => 210,'intensidade' => 3, 'duracao' => '2 horas',   'sintomas' => ['dor abdominal', 'distensão']],
            // Mês -7
            ['dias_atras' => 225,'intensidade' => 1, 'duracao' => '30 minutos','sintomas' => ['diarreia']],
            ['dias_atras' => 238,'intensidade' => 2, 'duracao' => '1 hora',    'sintomas' => ['cólica', 'náusea']],
        ];

        foreach ($crises as $c) {
            Crise::create([
                'user_id'           => $user->id,
                'data_hora'         => now()->subDays($c['dias_atras'])->setTime(rand(6, 22), rand(0, 59)),
                'sintomas'          => implode(', ', $c['sintomas']),
                'intensidade'       => $c['intensidade'],
                'duracao_estimada'  => $c['duracao'],
                'observacoes'       => $this->observacaoCrise($c['intensidade']),
            ]);
        }

        // ── Diário (últimos 3 meses, dias alternados) ───────────
        $anotacoes = [
            'Dia tranquilo, me senti bem. Comi com cuidado e não tive desconforto.',
            'Acordei com um leve desconforto abdominal mas melhorou ao longo do dia.',
            'Semana de muito estresse no trabalho. Percebo que isso piora os sintomas.',
            'Dormi bem, intestino funcionou normalmente. Fazendo dieta sem lactose.',
            'Tomei todos os remédios no horário. Consultei a nutricionista sobre fibras.',
            'Sintomas mais intensos hoje. Fui ao banheiro 4 vezes pela manhã.',
            'Me senti bem disposto. Caminhei 30 minutos à tarde.',
            'Introduzi brócolis na dieta com cautela. Sem reação adversa.',
            'Dor leve após o almoço. Pode ter sido a salada com tempero.',
            'Dia ótimo! Semana sem crises. Ânimo para continuar o tratamento.',
            'Fui à consulta. Dr. ajustou a dose da Mesalazina.',
            'Resultado do exame de sangue normal. Ferritina ainda um pouco baixa.',
            'Reintroduzi café com moderação. Vou observar a reação nos próximos dias.',
            'Cansaço intenso hoje. Dormi cedo. Sem outros sintomas.',
            'Boa semana. Mantive a dieta sem glúten e me senti melhor.',
        ];

        for ($i = 0; $i < 90; $i += rand(1, 3)) {
            $data = now()->subDays($i)->toDateString();
            if (!Diario::where('user_id', $user->id)->whereDate('data', $data)->exists()) {
                Diario::create([
                    'user_id'     => $user->id,
                    'data'        => $data,
                    'sintomas'    => $anotacoes[array_rand($anotacoes)],
                    'intensidade' => rand(1, 3),
                    'observacoes' => rand(0, 1) ? 'Tomei todos os medicamentos do dia.' : null,
                ]);
            }
        }

        // ── Consultas ────────────────────────────────────────────
        $consultas = [
            // Passadas
            ['dias' => -180, 'medico' => 'Dr. Carlos Mendes',    'especialidade' => 'Gastroenterologista', 'status' => 'realizada', 'local' => 'Hospital São Lucas',     'obs' => 'Ajuste da dose de Mesalazina. Solicitar colonoscopia.'],
            ['dias' => -120, 'medico' => 'Dra. Ana Figueiredo',  'especialidade' => 'Nutricionista',       'status' => 'realizada', 'local' => 'Clínica Bem Estar',      'obs' => 'Dieta sem lactose e baixo resíduo durante crises.'],
            ['dias' => -90,  'medico' => 'Dr. Carlos Mendes',    'especialidade' => 'Gastroenterologista', 'status' => 'realizada', 'local' => 'Hospital São Lucas',     'obs' => 'Paciente apresentando melhora. Manter Azatioprina.'],
            ['dias' => -60,  'medico' => 'Dra. Paula Ramos',     'especialidade' => 'Reumatologista',      'status' => 'realizada', 'local' => 'Consultório Particular', 'obs' => 'Avaliação articular. Sem manifestações extra-intestinais.'],
            ['dias' => -30,  'medico' => 'Dr. Carlos Mendes',    'especialidade' => 'Gastroenterologista', 'status' => 'realizada', 'local' => 'Hospital São Lucas',     'obs' => 'Colonoscopia mostrou inflamação leve no íleo terminal.'],
            ['dias' => -10,  'medico' => 'Dra. Ana Figueiredo',  'especialidade' => 'Nutricionista',       'status' => 'realizada', 'local' => 'Clínica Bem Estar',      'obs' => 'Reintrodução gradual de fibras solúveis. Probióticos.'],
            // Futuras
            ['dias' => 15,   'medico' => 'Dr. Carlos Mendes',    'especialidade' => 'Gastroenterologista', 'status' => 'agendada',  'local' => 'Hospital São Lucas',     'obs' => 'Retorno trimestral. Trazer exames de sangue recentes.'],
            ['dias' => 45,   'medico' => 'Dra. Ana Figueiredo',  'especialidade' => 'Nutricionista',       'status' => 'agendada',  'local' => 'Clínica Bem Estar',      'obs' => 'Avaliação do plano alimentar atual.'],
            ['dias' => 90,   'medico' => 'Dr. Carlos Mendes',    'especialidade' => 'Gastroenterologista', 'status' => 'agendada',  'local' => 'Hospital São Lucas',     'obs' => 'Consulta de acompanhamento semestral.'],
        ];

        foreach ($consultas as $c) {
            Consulta::create([
                'user_id'       => $user->id,
                'medico'        => $c['medico'],
                'especialidade' => $c['especialidade'],
                'data_hora'     => now()->addDays($c['dias'])->setTime(rand(8, 17), rand(0, 1) * 30),
                'local'         => $c['local'],
                'status'        => $c['status'],
                'observacoes'   => $c['obs'],
            ]);
        }

        // ── Exames ───────────────────────────────────────────────
        $exames = [
            // Passados
            ['dias' => -150, 'nome' => 'Hemograma Completo',        'tipo' => 'sangue',    'status' => 'realizado',  'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => 'Anemia leve. Ferritina 18 ng/mL.'],
            ['dias' => -150, 'nome' => 'PCR e VHS',                 'tipo' => 'sangue',    'status' => 'realizado',  'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => 'PCR 12 mg/L - inflamação moderada.'],
            ['dias' => -90,  'nome' => 'Colonoscopia',              'tipo' => 'endoscopia','status' => 'realizado',  'medico' => 'Dr. Carlos Mendes',   'local' => 'Hospital São Lucas',  'obs' => 'Inflamação leve no íleo terminal. Sem úlceras ativas.'],
            ['dias' => -60,  'nome' => 'Calprotectina Fecal',       'tipo' => 'fezes',     'status' => 'realizado',  'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => '320 µg/g - elevado. Indica atividade inflamatória.'],
            ['dias' => -30,  'nome' => 'Hemograma + Ferritina',     'tipo' => 'sangue',    'status' => 'realizado',  'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => 'Melhora da anemia. Ferritina 28 ng/mL.'],
            // Futuros
            ['dias' => 10,   'nome' => 'Hemograma + Calprotectina', 'tipo' => 'sangue',    'status' => 'agendado',   'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => 'Controle trimestral.'],
            ['dias' => 12,   'nome' => 'Vitamina D e B12',          'tipo' => 'sangue',    'status' => 'agendado',   'medico' => 'Dr. Carlos Mendes',   'local' => 'Laboratório Central', 'obs' => 'Monitorar deficiências nutricionais.'],
            ['dias' => 60,   'nome' => 'Enteroscopia por Cápsula',  'tipo' => 'endoscopia','status' => 'agendado',   'medico' => 'Dr. Carlos Mendes',   'local' => 'Hospital São Lucas',  'obs' => 'Avaliação do intestino delgado.'],
        ];

        foreach ($exames as $e) {
            Exame::create([
                'user_id'     => $user->id,
                'nome'        => $e['nome'],
                'tipo'        => $e['tipo'],
                'medico'      => $e['medico'],
                'data'        => now()->addDays($e['dias'])->setTime(rand(7, 16), rand(0, 1) * 30),
                'local'       => $e['local'],
                'status'      => $e['status'],
                'observacoes' => $e['obs'],
            ]);
        }

        // ── Avisos ───────────────────────────────────────────────
        $avisos = [
            ['titulo' => '⚠️ Estoque baixo: Prednisona',    'mensagem' => 'Você tem apenas 3 comprimidos de Prednisona. Reabasteça em breve para não perder doses.',                  'lido' => false],
            ['titulo' => '⚠️ Estoque baixo: Azatioprina',   'mensagem' => 'Restam 8 comprimidos de Azatioprina. Considere renovar a receita e reabastecer o estoque.',               'lido' => false],
            ['titulo' => '📅 Consulta em 15 dias',           'mensagem' => 'Lembrete: sua consulta com Dr. Carlos Mendes (Gastroenterologista) está agendada para daqui 15 dias.',    'lido' => false],
            ['titulo' => '🧪 Exame em 10 dias',              'mensagem' => 'Hemograma + Calprotectina agendado para daqui 10 dias no Laboratório Central. Jejum de 8 horas.',         'lido' => false],
            ['titulo' => '✅ Consulta realizada registrada', 'mensagem' => 'Sua consulta com Dra. Ana Figueiredo (Nutricionista) foi marcada como realizada.',                         'lido' => true],
            ['titulo' => '💊 Lembrete de dose',              'mensagem' => 'Não se esqueça de tomar a Mesalazina 800mg às 20:00.',                                                     'lido' => true],
            ['titulo' => '📋 Resultado de exame disponível','mensagem' => 'Hemograma + Ferritina realizado. Melhora da anemia detectada. Compartilhe com seu médico.',                'lido' => true],
            ['titulo' => '🎉 30 dias sem crise intensa',     'mensagem' => 'Parabéns! Você completou 30 dias sem registrar crise de intensidade 4 ou 5. Continue assim!',             'lido' => true],
        ];

        foreach ($avisos as $a) {
            Aviso::create([
                'user_id'  => $user->id,
                'titulo'   => $a['titulo'],
                'mensagem' => $a['mensagem'],
                'lido'     => $a['lido'],
            ]);
        }

        $this->command->info('✅ Usuário de teste criado com sucesso!');
        $this->command->info('   Email: teste@crohncare.com');
        $this->command->info('   Senha: teste123');
        $this->command->info('   Dados: 5 remédios, 19 crises, ~45 diários, 9 consultas, 8 exames, 8 avisos');
    }

    private function observacaoCrise(int $intensidade): ?string
    {
        return match (true) {
            $intensidade >= 5 => 'Crise muito intensa. Precisei deitar e usar compressa quente.',
            $intensidade >= 4 => 'Crise forte. Fiquei em casa e tomei Buscopan.',
            $intensidade >= 3 => 'Crise moderada. Evitei alimentos sólidos por algumas horas.',
            $intensidade >= 2 => 'Desconforto leve. Passou sozinho em seguida.',
            default           => null,
        };
    }
}
