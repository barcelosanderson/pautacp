<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Criar conta e entrar usando só nome, sobrenome e um código:
 * - o código dos professores abre conta de professor;
 * - o código da coordenação abre conta da coordenação (que pode editar a pauta).
 * Depois de entrar, o aparelho fica lembrado: a pessoa não precisa entrar de novo.
 */
class AcessoController extends Controller
{
    private const NOME_REPETIDO = 'Já existe uma conta com esse nome nesta escola. Se a conta é sua, clique em “Já tenho conta”.';

    public function mostrar(Request $request): View
    {
        $modo = old('modo', $request->query('modo')) === 'entrar' ? 'entrar' : 'criar';

        return view('acesso.entrar', ['modo' => $modo]);
    }

    public function criarConta(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        [$escola, $papel] = Escola::acessoPorCodigo($dados['codigo']) ?? [null, null];
        if (! $escola) {
            return $this->codigoNaoEncontrado();
        }

        $chave = User::chaveDe($dados['nome'], $dados['sobrenome']);
        if ($escola->usuarios()->where('chave', $chave)->exists()) {
            return back()->withInput()->withErrors(['nome' => self::NOME_REPETIDO]);
        }

        try {
            $usuario = $escola->usuarios()->create([
                'nome' => $dados['nome'],
                'sobrenome' => $dados['sobrenome'],
                'papel' => $papel,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors(['nome' => self::NOME_REPETIDO]);
        }

        Auth::login($usuario, remember: true);
        $request->session()->regenerate();

        return redirect()->to($this->inicioDe($usuario))->with('sucesso', $usuario->ehCoordenador()
            ? 'Conta da coordenação criada. Seja bem-vindo(a), '.$usuario->nome.'.'
            : 'Conta criada com sucesso. Seja bem-vindo(a), '.$usuario->nome.'.');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        [$escola, $papel] = Escola::acessoPorCodigo($dados['codigo']) ?? [null, null];
        if (! $escola) {
            return $this->codigoNaoEncontrado();
        }

        $usuario = $escola->usuarios()
            ->where('chave', User::chaveDe($dados['nome'], $dados['sobrenome']))
            ->first();

        if (! $usuario) {
            return back()->withInput()->withErrors([
                'nome' => 'Não encontramos uma conta com esse nome nesta escola. Confira como você escreveu ou crie a sua conta.',
            ]);
        }

        // Conta da coordenação não entra com o código dos professores.
        if ($usuario->ehCoordenador() && $papel === User::PAPEL_PROFESSOR) {
            return back()->withInput()->withErrors([
                'codigo' => 'Esta conta é da coordenação. Para entrar, use o código da coordenação.',
            ]);
        }

        // Quem entra com o código da coordenação passa a ser da coordenação.
        if ($papel === User::PAPEL_COORDENADOR && ! $usuario->ehCoordenador()) {
            $usuario->update(['papel' => User::PAPEL_COORDENADOR]);
        }

        Auth::login($usuario, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($this->inicioDe($usuario))
            ->with('sucesso', 'Que bom ver você de novo, '.$usuario->nome.'.');
    }

    public function sair(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', ['modo' => 'entrar'])
            ->with('sucesso', 'Você saiu da sua conta.');
    }

    /** A coordenação começa na tela de editar a pauta; o professor, na pauta dele. */
    private function inicioDe(User $usuario): string
    {
        return $usuario->ehCoordenador() ? route('coordenacao.tarefas.index') : route('pauta');
    }

    /**
     * @return array{nome: string, sobrenome: string, codigo: string}
     */
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:60'],
            'sobrenome' => ['required', 'string', 'max:80'],
            'codigo' => ['required', 'string', 'max:40'],
        ], [
            'nome.required' => 'Escreva o seu nome.',
            'sobrenome.required' => 'Escreva o seu sobrenome.',
            'codigo.required' => 'Escreva o código da escola.',
            'nome.max' => 'O nome pode ter no máximo 60 letras.',
            'sobrenome.max' => 'O sobrenome pode ter no máximo 80 letras.',
            'codigo.max' => 'Esse código está grande demais. Confira o código ou fale com o cara.',
        ]);
    }

    private function codigoNaoEncontrado(): RedirectResponse
    {
        return back()->withInput()->withErrors([
            'codigo' => 'Não encontramos uma escola com esse código. Confira o código ou fale com o cara.',
        ]);
    }
}
