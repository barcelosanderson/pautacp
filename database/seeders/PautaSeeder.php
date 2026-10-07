<?php

namespace Database\Seeders;

use App\Models\Escola;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Cria a escola (com o código de acesso) e a pauta do 4º bimestre.
 * Pode rodar mais de uma vez: se a escola já tem tarefas, nada é duplicado.
 */
class PautaSeeder extends Seeder
{
    private const MAPEAMENTO = 'Entregar o mapeamento de classe por e-mail e deixar uma cópia impressa na sala.';

    private const ANIVERSARIANTES = 'Homenagem aos aniversariantes do mês, junto com os representantes da turma, e envio das fotos.';

    /**
     * [como aparece, início (mês-dia), prazo (mês-dia), descrição, opções]
     * O prazo é o último dia: é ele que define se a tarefa está vencida.
     */
    private const TAREFAS = [
        // Outubro
        ['01/10', '10-01', '10-01', 'Enviar os conteúdos da Avaliação Mensal e os critérios da Avaliação em Processo (4,0) por e-mail à coordenação, em folha com logo.'],
        ['02/10', '10-02', '10-02', self::MAPEAMENTO, ['repete' => true]],
        ['05/10', '10-05', '10-05', 'Enviar a foto dos conselheiros com a turma e um texto para postagem.'],
        ['06/10', '10-06', '10-06', 'Abertura do bimestre com a dinâmica “Caixa dos Desejos” na primeira aula. Abrir a caixa, fazer um cartaz com os dados e conversar sobre postura (“o que depende de mim / dos outros”).'],
        ['06 e 07/10', '10-06', '10-10', 'Simulado Treineiro e ENEM vol. 4, para o Ensino Médio, à tarde. Corrigir as redações até 09/10 e escanear e enviar até 10/10.'],
        ['06 a 09/10', '10-06', '10-09', 'Recado de boas-vindas pelo Master. Cada conselheiro envia o seu, com o tema “a capacidade humana de adaptar-se e vencer”.'],
        ['Até 06/10', '10-06', '10-06', self::ANIVERSARIANTES, ['repete' => true]],
        ['09/10', '10-09', '10-09', 'Entregar o plano bimestral do 4º bimestre e enviar a Avaliação Mensal (6,0). A data da avaliação precisa constar no planejamento.'],
        ['10/10', '10-10', '10-10', 'Digitação final das notas do 3º bimestre.'],
        ['15/10', '10-15', '10-15', 'Viagem à Usina Cândido Motta. Registrar como reunião pedagógica, com presença de todos.'],
        ['20/10', '10-20', '10-20', 'HTPC “Valores e Virtudes”, além da prova da beca e das fotos.'],
        ['26/10', '10-26', '10-26', 'Recado motivacional individual de cada conselheiro para a turma.'],
        ['26 a 30/10', '10-26', '10-30', 'Grande Ação: recado no Master sobre o projeto, o Pacto Educativo Global, com foco em “cuidar da casa comum”.'],
        ['29/10', '10-29', '10-29', 'Enviar os conteúdos da avaliação bimestral até 12h, em Arial 12 e folha com logo. No mesmo dia, enviar os conteúdos adaptados e os da recuperação semestral, via Ocorrências/Combinados.'],

        // Novembro
        ['02/11', '11-02', '11-02', self::MAPEAMENTO, ['repete' => true]],
        ['03 e 04/11', '11-03', '11-04', 'Repositiva mensal no contraturno. Matemática é no dia 03.'],
        ['05/11', '11-05', '11-05', 'Revisou/Passou e prazo final da digitação das notas mensais.'],
        ['Até 06/11', '11-06', '11-06', self::ANIVERSARIANTES, ['repete' => true]],
        ['06/11', '11-06', '11-06', 'Enviar as avaliações bimestrais regulares (7,0 no Fund. II e 6,0 no Médio) e as adaptadas.'],
        ['09/11', '11-09', '11-09', 'Recado de “boas provas” de cada conselheiro para a turma.'],
        ['23/11', '11-23', '11-26', 'Simulado SAE vol. 4, valendo 3,0. Escanear e enviar até 25/11 e corrigir as redações até 26/11.'],
        ['26/11', '11-26', '11-26', 'Missa de Ação de Graças (formatura e aniversário do colégio), às 19h30, na Paróquia Nossa Senhora Mãe da Igreja.'],
        ['30/11', '11-30', '11-30', 'Finalização do Trote Solidário: arrecadação de balas, bombons e pirulitos, com fantasia “Natal todo dia” em verde e vermelho. Também é o prazo para entregar a lista de alunos da recuperação semestral.'],

        // Dezembro
        ['01 e 02/12', '12-01', '12-02', 'Repositiva bimestral. Matemática é no dia 01.'],
        ['02/12', '12-02', '12-02', self::MAPEAMENTO, ['repete' => true]],
        ['03 e 04/12', '12-03', '12-04', 'Recuperação semestral (10,0). Matemática é no dia 03.'],
        ['04/12', '12-04', '12-04', 'Conselho de classe e prazo final da digitação bimestral.'],
        ['Até 06/12', '12-06', '12-06', self::ANIVERSARIANTES, ['repete' => true]],
        ['07/12', '12-07', '12-07', 'Digitação das notas finais. Não há aula, mas é preciso registrar aula e frequência.'],
        ['09 a 11/12', '12-09', '12-11', 'Recuperação final. Matemática é no dia 09.'],
        ['15/12', '12-15', '12-15', 'Conselho final.'],
        ['16 a 18/12', '12-16', '12-18', 'Planejamento 2027, sem aula. Registrar aula e frequência.'],
        ['17 e 18/12', '12-17', '12-18', 'Formatura do 3º ano (17) e do 9º ano (18).'],
        ['21/12 a 17/01', '12-21', '01-17', 'Recesso. A volta às aulas é em 25/01.', ['checavel' => false]],
    ];

    public function run(): void
    {
        $codigo = Escola::normalizarCodigo((string) (config('pauta.escola.codigo') ?: Escola::sortearCodigo()));

        $escola = Escola::firstOrCreate(
            ['codigo' => $codigo],
            ['nome' => config('pauta.escola.nome'), 'codigo_coordenacao' => $this->codigoDaCoordenacao($codigo)],
        );

        if ($escola->tarefas()->exists()) {
            $this->command?->info("A escola {$escola->nome} já tem tarefas. Nada foi alterado.");
            $this->mostrarCodigos($escola);

            return;
        }

        $ano = (int) config('pauta.ano');

        foreach (self::TAREFAS as $ordem => $linha) {
            [$quando, $inicio, $prazo, $descricao] = $linha;
            $opcoes = $linha[4] ?? [];

            $dataInicio = $this->data($ano, $inicio);
            $dataPrazo = $this->data($ano, $prazo);
            if ($dataPrazo->lt($dataInicio)) {
                $dataPrazo->addYear(); // ex.: recesso de 21/12 a 17/01
            }

            $escola->tarefas()->create([
                'quando' => $quando,
                'inicio' => $dataInicio,
                'prazo' => $dataPrazo,
                'descricao' => $descricao,
                'repete_todo_mes' => $opcoes['repete'] ?? false,
                'checavel' => $opcoes['checavel'] ?? true,
                'ordem' => $ordem,
            ]);
        }

        $this->command?->info('Pauta criada com '.count(self::TAREFAS)." itens para a escola {$escola->nome}.");
        $this->mostrarCodigos($escola);
    }

    /** Código da coordenação do .env, se for válido; senão um sorteado (mais longo que o dos professores). */
    private function codigoDaCoordenacao(string $codigoProfessores): string
    {
        $doEnv = Escola::normalizarCodigo((string) config('pauta.escola.codigo_coordenacao'));

        if ($doEnv !== '' && $doEnv !== $codigoProfessores && ! Escola::codigoEmUso($doEnv)) {
            return $doEnv;
        }

        if ($doEnv !== '') {
            $this->command?->warn('ESCOLA_CODIGO_COORDENACAO é igual ao código dos professores ou já está em uso. Um código novo foi sorteado.');
        }

        return Escola::sortearCodigo(8);
    }

    private function mostrarCodigos(Escola $escola): void
    {
        $this->command?->info("Código dos professores: {$escola->codigo}");
        $this->command?->info("Código da coordenação: {$escola->codigo_coordenacao}");
    }

    private function data(int $ano, string $mesDia): Carbon
    {
        [$mes, $dia] = array_map('intval', explode('-', $mesDia));

        return Carbon::create($ano, $mes, $dia)->startOfDay();
    }
}
