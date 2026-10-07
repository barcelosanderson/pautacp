@extends('layouts.app')

@section('titulo', $modo === 'entrar' ? 'Entrar' : 'Criar conta')

@section('conteudo')
<main class="pagina pagina-estreita">
    <span class="marca">{{ config('app.name') }}</span>

    <section class="card card-formulario">
        <nav class="filtros filtros-iguais" aria-label="O que você quer fazer?">
            <a href="{{ route('login') }}" class="botao" @if ($modo === 'criar') aria-current="page" @endif>Criar conta</a>
            <a href="{{ route('login', ['modo' => 'entrar']) }}" class="botao" @if ($modo === 'entrar') aria-current="page" @endif>Já tenho conta</a>
        </nav>

        <div class="pilha-pequena">
            @if ($modo === 'entrar')
                <h1 class="titulo-pagina">Entrar na minha conta</h1>
                <p class="texto">Escreva o seu nome, o seu sobrenome e o código da escola, do mesmo jeito que na hora de criar a conta.</p>
            @else
                <h1 class="titulo-pagina">Criar minha conta</h1>
                <p class="texto">Você só precisa do seu nome, do seu sobrenome e do código da escola.</p>
            @endif
        </div>

        <form method="post" action="{{ $modo === 'entrar' ? route('entrar') : route('criar-conta') }}" class="formulario" novalidate>
            @csrf
            <input type="hidden" name="modo" value="{{ $modo }}">

            <div class="grade-campos">
                <div class="campo">
                    <label for="nome" class="rotulo">Nome</label>
                    <input id="nome" name="nome" type="text" class="entrada" value="{{ old('nome') }}"
                        autocomplete="given-name" maxlength="60"
                        @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror>
                    @error('nome')
                        <span id="nome-erro" class="mensagem-erro">{{ $message }}</span>
                    @enderror
                </div>

                <div class="campo">
                    <label for="sobrenome" class="rotulo">Sobrenome</label>
                    <input id="sobrenome" name="sobrenome" type="text" class="entrada" value="{{ old('sobrenome') }}"
                        autocomplete="family-name" maxlength="80"
                        @error('sobrenome') aria-invalid="true" aria-describedby="sobrenome-erro" @enderror>
                    @error('sobrenome')
                        <span id="sobrenome-erro" class="mensagem-erro">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="campo">
                <label for="codigo" class="rotulo">Código da escola</label>
                <input id="codigo" name="codigo" type="text" class="entrada entrada-codigo" value="{{ old('codigo') }}"
                    autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="40"
                    aria-describedby="@error('codigo') codigo-erro @enderror codigo-ajuda"
                    @error('codigo') aria-invalid="true" @enderror>
                @error('codigo')
                    <span id="codigo-erro" class="mensagem-erro">{{ $message }}</span>
                @enderror
                <span id="codigo-ajuda" class="ajuda">O código é entregue pessoalmente. Se ainda não tem, fale com o cara.</span>
            </div>

            <button type="submit" class="botao botao-principal botao-grande">
                {{ $modo === 'entrar' ? 'Entrar' : 'Criar minha conta' }}
            </button>
        </form>
    </section>

    <p class="texto-secundario texto-centro">Precisa de ajuda? Fale com o cara.</p>
</main>
@endsection
