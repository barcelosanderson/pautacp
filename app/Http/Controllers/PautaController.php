<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Services\Pauta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PautaController extends Controller
{
    public function index(Request $request): View
    {
        $hoje = now();
        $pauta = new Pauta($request->user(), $hoje);
        $meses = $pauta->meses();

        // Mês aberto: o que a pessoa escolheu, senão o mês atual, senão todos.
        $pedido = $request->query('mes');
        $escolhido = match (true) {
            $pedido === 'todos' => 'todos',
            is_string($pedido) && $meses->has($pedido) => $pedido,
            $meses->has($hoje->format('Y-m')) => $hoje->format('Y-m'),
            default => 'todos',
        };

        $filtros = $meses
            ->map(fn (object $mes) => [
                'rotulo' => $mes->nome,
                'url' => route('pauta', ['mes' => $mes->chave]),
                'ativo' => $escolhido === $mes->chave,
            ])
            ->values()
            ->push([
                'rotulo' => 'Todos os meses',
                'url' => route('pauta', ['mes' => 'todos']),
                'ativo' => $escolhido === 'todos',
            ]);

        return view('pauta.index', [
            'hoje' => $hoje,
            'resumo' => $pauta->resumo(),
            'vazia' => $meses->isEmpty(),
            'filtros' => $filtros,
            'meses' => $escolhido === 'todos' ? $meses : $meses->only([$escolhido]),
        ]);
    }

    public function marcar(Request $request, Tarefa $tarefa): JsonResponse|RedirectResponse
    {
        $usuario = $request->user();

        abort_unless((int) $tarefa->escola_id === (int) $usuario->escola_id && $tarefa->checavel, 404);

        $feita = (bool) $request->validate(['feita' => ['required', 'boolean']])['feita'];

        if ($feita) {
            $usuario->tarefasFeitas()->syncWithoutDetaching([$tarefa->id]);
        } else {
            $usuario->tarefasFeitas()->detach($tarefa->id);
        }

        $mensagem = $feita ? 'Tarefa marcada como feita.' : 'Tarefa desmarcada.';

        if (! $request->expectsJson()) {
            return back()->with('sucesso', $mensagem);
        }

        $hoje = now();

        return response()->json([
            'feita' => $feita,
            'status' => $tarefa->status($feita, $hoje),
            'mensagem' => $mensagem,
            'resumo' => (new Pauta($usuario, $hoje))->resumo(),
        ]);
    }
}
