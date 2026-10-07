<?php

namespace App\Http\Controllers\Coordenacao;

use App\Http\Controllers\Controller;
use App\Models\Tarefa;
use App\Models\User;
use App\Services\Pauta;
use App\Support\FiltroDeMes;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Tela "Editar pauta": a coordenação acrescenta, muda e exclui itens.
 * As mudanças aparecem na hora para todos os professores da escola.
 */
class TarefaController extends Controller
{
    public function index(Request $request): View
    {
        $escola = $request->user()->escola;

        $tarefas = $escola->tarefas()
            ->withCount(['concluidaPor as feita_por' => fn ($consulta) => $consulta->where('papel', User::PAPEL_PROFESSOR)])
            ->orderBy('inicio')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $meses = $tarefas
            ->groupBy(fn (Tarefa $tarefa) => $tarefa->inicio->format('Y-m'))
            ->map(fn (Collection $lista, string $chave) => (object) [
                'chave' => $chave,
                'nome' => Pauta::nomeDoMes($chave),
                'tarefas' => $lista,
            ]);

        $filtro = new FiltroDeMes($meses, $request->query('mes'), now(), 'coordenacao.tarefas.index');

        return view('coordenacao.tarefas.index', [
            'vazia' => $meses->isEmpty(),
            'filtros' => $filtro->botoes(),
            'meses' => $filtro->visiveis(),
            'professores' => $escola->usuarios()->where('papel', User::PAPEL_PROFESSOR)->count(),
        ]);
    }

    public function create(): View
    {
        return view('coordenacao.tarefas.formulario', ['tarefa' => new Tarefa(['checavel' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $escola = $request->user()->escola;

        $tarefa = $escola->tarefas()->create($this->dados($request) + [
            'ordem' => (int) $escola->tarefas()->max('ordem') + 1,
        ]);

        return $this->voltarParaOMes($tarefa->inicio, 'Item acrescentado à pauta.');
    }

    public function edit(Request $request, Tarefa $tarefa): View
    {
        $this->garantirDaEscola($request, $tarefa);

        return view('coordenacao.tarefas.formulario', ['tarefa' => $tarefa]);
    }

    public function update(Request $request, Tarefa $tarefa): RedirectResponse
    {
        $this->garantirDaEscola($request, $tarefa);

        $tarefa->update($this->dados($request));

        return $this->voltarParaOMes($tarefa->inicio, 'Item atualizado.');
    }

    public function destroy(Request $request, Tarefa $tarefa): RedirectResponse
    {
        $this->garantirDaEscola($request, $tarefa);

        $inicio = $tarefa->inicio;
        $tarefa->delete(); // as marcações dos professores saem junto

        return $this->voltarParaOMes($inicio, 'Item excluído da pauta.');
    }

    private function garantirDaEscola(Request $request, Tarefa $tarefa): void
    {
        abort_unless((int) $tarefa->escola_id === (int) $request->user()->escola_id, 404);
    }

    private function voltarParaOMes(CarbonInterface $data, string $mensagem): RedirectResponse
    {
        return redirect()
            ->route('coordenacao.tarefas.index', ['mes' => $data->format('Y-m')])
            ->with('sucesso', $mensagem);
    }

    /**
     * @return array{inicio: Carbon, prazo: Carbon, quando: string, descricao: string, repete_todo_mes: bool, checavel: bool}
     */
    private function dados(Request $request): array
    {
        $dados = $request->validate([
            'inicio' => ['required', 'date'],
            'prazo' => ['nullable', 'date', 'after_or_equal:inicio'],
            'quando' => ['nullable', 'string', 'max:40'],
            'descricao' => ['required', 'string', 'max:2000'],
        ], [
            'inicio.required' => 'Escolha o dia.',
            'inicio.date' => 'Escolha uma data válida.',
            'prazo.date' => 'Escolha uma data válida.',
            'prazo.after_or_equal' => 'O último dia não pode ser antes do primeiro.',
            'quando.max' => 'Use no máximo 40 letras.',
            'descricao.required' => 'Escreva o que precisa ser feito.',
            'descricao.max' => 'O texto pode ter no máximo 2000 letras.',
        ]);

        $inicio = Carbon::parse($dados['inicio'])->startOfDay();
        $prazo = ! empty($dados['prazo']) ? Carbon::parse($dados['prazo'])->startOfDay() : $inicio->copy();
        $quando = trim((string) ($dados['quando'] ?? ''));

        return [
            'inicio' => $inicio,
            'prazo' => $prazo,
            'quando' => $quando !== '' ? $quando : Tarefa::rotuloDasDatas($inicio, $prazo),
            'descricao' => trim($dados['descricao']),
            'repete_todo_mes' => $request->boolean('repete_todo_mes'),
            'checavel' => ! $request->boolean('aviso'),
        ];
    }
}
