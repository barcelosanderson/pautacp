@extends('layouts.app')

@php
    $editando = $tarefa->exists;
    $comErros = $errors->any();
@endphp

@section('titulo', $editando ? 'Editar item' : 'Acrescentar item')

@section('conteudo')
<div class="pagina pagina-larga">
    @include('partials.menu')

    <main class="pilha pilha-pauta">
        <section class="card card-formulario">
            <div class="pilha-pequena">
                <h1 class="titulo-pagina">{{ $editando ? 'Editar item da pauta' : 'Acrescentar item na pauta' }}</h1>
                <p class="texto">Preencha o dia e o que precisa ser feito. Ao salvar, o item aparece para todos os professores.</p>
            </div>

            <form method="post" class="formulario formulario-limitado" novalidate
                action="{{ $editando ? route('coordenacao.tarefas.update', $tarefa) : route('coordenacao.tarefas.store') }}">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <div class="grade-campos">
                    <div class="campo">
                        <label for="inicio" class="rotulo">Primeiro dia</label>
                        <input id="inicio" name="inicio" type="date" class="entrada"
                            value="{{ old('inicio', $tarefa->inicio?->format('Y-m-d')) }}"
                            @error('inicio') aria-invalid="true" aria-describedby="inicio-erro" @enderror>
                        @error('inicio')
                            <span id="inicio-erro" class="mensagem-erro">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo">
                        <label for="prazo" class="rotulo">Último dia (prazo)</label>
                        <input id="prazo" name="prazo" type="date" class="entrada"
                            value="{{ old('prazo', $editando && ! $tarefa->inicio->isSameDay($tarefa->prazo) ? $tarefa->prazo->format('Y-m-d') : '') }}"
                            aria-describedby="@error('prazo') prazo-erro @enderror prazo-ajuda"
                            @error('prazo') aria-invalid="true" @enderror>
                        @error('prazo')
                            <span id="prazo-erro" class="mensagem-erro">{{ $message }}</span>
                        @enderror
                        <span id="prazo-ajuda" class="ajuda">Deixe em branco se for um dia só.</span>
                    </div>
                </div>

                <div class="campo">
                    <label for="quando" class="rotulo">Como a data aparece (opcional)</label>
                    <input id="quando" name="quando" type="text" class="entrada" maxlength="40"
                        value="{{ old('quando', $editando ? $tarefa->quandoPersonalizado() : '') }}"
                        aria-describedby="@error('quando') quando-erro @enderror quando-ajuda"
                        @error('quando') aria-invalid="true" @enderror>
                    @error('quando')
                        <span id="quando-erro" class="mensagem-erro">{{ $message }}</span>
                    @enderror
                    <span id="quando-ajuda" class="ajuda">Deixe em branco para montar sozinho, como “06 a 09/10”. Use só para textos como “Até 06/10”.</span>
                </div>

                <div class="campo">
                    <label for="descricao" class="rotulo">O que fazer</label>
                    <textarea id="descricao" name="descricao" class="entrada area-texto" rows="4" maxlength="2000"
                        @error('descricao') aria-invalid="true" aria-describedby="descricao-erro" @enderror>{{ old('descricao', $tarefa->descricao) }}</textarea>
                    @error('descricao')
                        <span id="descricao-erro" class="mensagem-erro">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pilha">
                    <label for="repete_todo_mes" class="opcao">
                        <input id="repete_todo_mes" name="repete_todo_mes" type="checkbox" value="1" class="caixa"
                            @checked($comErros ? old('repete_todo_mes') : $tarefa->repete_todo_mes)>
                        <span class="caixa-visual"><x-icone nome="certo" tamanho="16" espessura="3.5" /></span>
                        <span class="opcao-texto">
                            <span>Repete todo mês</span>
                            <span class="ajuda">Mostra a etiqueta “Repete todo mês”. Crie um item para cada mês.</span>
                        </span>
                    </label>

                    <label for="aviso" class="opcao">
                        <input id="aviso" name="aviso" type="checkbox" value="1" class="caixa"
                            @checked($comErros ? old('aviso') : ! $tarefa->checavel)>
                        <span class="caixa-visual"><x-icone nome="certo" tamanho="16" espessura="3.5" /></span>
                        <span class="opcao-texto">
                            <span>É só um aviso</span>
                            <span class="ajuda">Como o recesso: aparece na pauta, mas sem caixa para marcar.</span>
                        </span>
                    </label>
                </div>

                <div class="acoes">
                    <button type="submit" class="botao botao-principal">Salvar item</button>
                    <a href="{{ route('coordenacao.tarefas.index', $editando ? ['mes' => $tarefa->inicio->format('Y-m')] : []) }}" class="botao botao-secundario">Cancelar</a>
                </div>
            </form>
        </section>

        @if ($editando)
            <section class="card">
                <h2 class="titulo-card">Excluir este item</h2>
                <p class="texto">O item sai da pauta de todos os professores.</p>
                <div class="acoes">
                    <button type="button" class="botao botao-perigo"
                        data-excluir
                        data-url="{{ route('coordenacao.tarefas.destroy', $tarefa) }}"
                        data-nome="{{ $tarefa->quando }} · {{ \Illuminate\Support\Str::limit($tarefa->descricao, 80) }}">Excluir item</button>
                </div>
            </section>
        @endif
    </main>
</div>

@if ($editando)
    @include('partials.popup-excluir')
@endif
@endsection
