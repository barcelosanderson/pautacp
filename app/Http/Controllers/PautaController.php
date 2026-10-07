<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Services\Pauta;
use App\Support\FiltroDeMes;
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
        $filtro = new FiltroDeMes($meses, $request->query('mes'), $hoje, 'pauta');

        return view('pauta.index', [
            'hoje' => $hoje,
            'resumo' => $pauta->resumo(),
            'vazia' => $meses->isEmpty(),
            'filtros' => $filtro->botoes(),
            'meses' => $filtro->visiveis(),
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
