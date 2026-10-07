@extends('layouts.app')

@section('titulo', 'Códigos de acesso')

@section('conteudo')
<div class="pagina pagina-larga">
    @include('partials.menu')

    <main class="pilha pilha-pauta">
        <section class="card">
            <h1 class="titulo-pagina">Códigos de acesso</h1>
            <p class="texto">Cada código abre um tipo de conta. Se um código for parar com quem não deveria, troque por um novo.</p>
            <p class="texto-secundario">Quem já entrou continua com acesso no mesmo aparelho. O código novo vale para criar contas e para entrar em aparelhos novos.</p>
        </section>

        <div class="grade-cards grade-cards-largos">
            @foreach ([
                'professores' => [
                    'titulo' => 'Código dos professores',
                    'texto' => 'Passe este código para os professores criarem a conta.',
                    'valor' => $escola->codigo,
                ],
                'coordenacao' => [
                    'titulo' => 'Código da coordenação',
                    'texto' => 'Só para quem edita a pauta. Não passe este código para os professores.',
                    'valor' => $escola->codigo_coordenacao,
                ],
            ] as $tipo => $codigo)
                @php($campo = 'novo_codigo_'.$tipo)
                <section class="card" aria-labelledby="titulo-{{ $tipo }}">
                    <h2 class="titulo-card" id="titulo-{{ $tipo }}">{{ $codigo['titulo'] }}</h2>
                    <p class="texto">{{ $codigo['texto'] }}</p>
                    <p class="codigo-destaque" aria-label="{{ $codigo['titulo'] }}: {{ implode(' ', str_split((string) $codigo['valor'])) }}">{{ $codigo['valor'] }}</p>

                    <form method="post" action="{{ route('coordenacao.codigos.trocar', $tipo) }}" class="formulario" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="campo">
                            <label for="{{ $campo }}" class="rotulo">Novo código</label>
                            <input id="{{ $campo }}" name="{{ $campo }}" type="text" class="entrada entrada-codigo"
                                value="{{ old($campo) }}" maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false"
                                aria-describedby="@error($campo) {{ $campo }}-erro @enderror {{ $campo }}-ajuda"
                                @error($campo) aria-invalid="true" @enderror>
                            @error($campo)
                                <span id="{{ $campo }}-erro" class="mensagem-erro">{{ $message }}</span>
                            @enderror
                            <span id="{{ $campo }}-ajuda" class="ajuda">De 4 a 20 letras ou números, sem acentos.</span>
                        </div>
                        <button type="submit" class="botao botao-principal">Trocar {{ \Illuminate\Support\Str::lower($codigo['titulo']) }}</button>
                    </form>
                </section>
            @endforeach
        </div>
    </main>
</div>
@endsection
