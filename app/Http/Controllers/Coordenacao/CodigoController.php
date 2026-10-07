<?php

namespace App\Http\Controllers\Coordenacao;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tela "Códigos de acesso": ver e trocar o código dos professores
 * e o código da coordenação.
 */
class CodigoController extends Controller
{
    private const TIPOS = [
        'professores' => ['campo' => 'codigo', 'outro' => 'codigo_coordenacao', 'nome' => 'dos professores', 'nome_outro' => 'da coordenação'],
        'coordenacao' => ['campo' => 'codigo_coordenacao', 'outro' => 'codigo', 'nome' => 'da coordenação', 'nome_outro' => 'dos professores'],
    ];

    public function mostrar(Request $request): View
    {
        return view('coordenacao.codigos', ['escola' => $request->user()->escola]);
    }

    public function trocar(Request $request, string $tipo): RedirectResponse
    {
        $escola = $request->user()->escola;
        $tipoDeCodigo = self::TIPOS[$tipo];
        $entrada = 'novo_codigo_'.$tipo;

        // Valida já sem espaços e em maiúsculas, do jeito que vai ficar salvo.
        $request->merge([$entrada => Escola::normalizarCodigo((string) $request->input($entrada))]);

        $novo = $request->validate([
            $entrada => [
                'required',
                'min:4',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                function (string $atributo, mixed $valor, Closure $falhar) use ($escola, $tipoDeCodigo) {
                    if ($valor === $escola->{$tipoDeCodigo['outro']}) {
                        $falhar('Este código é igual ao código '.$tipoDeCodigo['nome_outro'].'. Use um código diferente.');
                    } elseif (Escola::codigoEmUso($valor, ignorarEscolaId: $escola->id)) {
                        $falhar('Este código já está em uso. Escolha outro.');
                    }
                },
            ],
        ], [
            "{$entrada}.required" => 'Escreva o novo código.',
            "{$entrada}.min" => 'Use pelo menos 4 letras ou números.',
            "{$entrada}.max" => 'Use no máximo 20 letras ou números.',
            "{$entrada}.regex" => 'Use só letras e números, sem acentos.',
        ])[$entrada];

        $escola->update([$tipoDeCodigo['campo'] => $novo]);

        return redirect()->route('coordenacao.codigos')
            ->with('sucesso', 'Código '.$tipoDeCodigo['nome'].' trocado.');
    }
}
