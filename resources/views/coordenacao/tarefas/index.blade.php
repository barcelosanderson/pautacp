@extends('layouts.app')

@section('titulo', 'Editar pauta')

@section('conteudo')
<div class="pagina pagina-larga">
    @include('partials.menu')

    <main class="pilha pilha-pauta">
        <section class="card">
            <h1 class="titulo-pagina">Editar pauta</h1>
            <p class="texto">Acrescente, mude ou exclua itens. As mudanças aparecem na hora para todos os professores.</p>
            <div class="acoes">
                <a href="{{ route('coordenacao.tarefas.create') }}" class="botao botao-principal">Acrescentar item</a>
            </div>
        </section>

        @if ($vazia)
            <section class="card vazio">
                <h2 class="titulo-card">Nenhum item na pauta ainda.</h2>
                <p class="texto">Clique em “Acrescentar item” para criar o primeiro.</p>
            </section>
        @else
            <nav class="filtros" aria-label="Escolha o mês">
                @foreach ($filtros as $filtro)
                    <a href="{{ $filtro['url'] }}" class="botao" @if ($filtro['ativo']) aria-current="page" @endif>{{ $filtro['rotulo'] }}</a>
                @endforeach
            </nav>

            @foreach ($meses as $mes)
                <section class="mes" aria-labelledby="mes-{{ $mes->chave }}">
                    <div class="mes-cabecalho">
                        <h2 class="titulo-pagina" id="mes-{{ $mes->chave }}">{{ $mes->nome }}</h2>
                        <span class="texto-secundario">{{ $mes->tarefas->count() }} {{ $mes->tarefas->count() === 1 ? 'item' : 'itens' }}</span>
                    </div>

                    @foreach ($mes->tarefas as $tarefa)
                        <article class="linha-edicao @unless ($tarefa->checavel) linha-edicao-aviso @endunless">
                            <div class="tarefa-corpo">
                                <div class="tarefa-topo">
                                    <span class="tarefa-data">{{ $tarefa->quando }}</span>
                                    @if ($tarefa->repete_todo_mes)
                                        <span class="selo selo-suave">Repete todo mês</span>
                                    @endif
                                    @unless ($tarefa->checavel)
                                        <span class="selo">Só aviso</span>
                                    @endunless
                                </div>
                                <p class="tarefa-texto">{{ $tarefa->descricao }}</p>
                                @if ($tarefa->checavel)
                                    <p class="texto-secundario">
                                        Feito por {{ $tarefa->feita_por }} de {{ $professores }} {{ $professores === 1 ? 'professor' : 'professores' }}
                                    </p>
                                @endif
                            </div>
                            <div class="linha-edicao-botoes">
                                <a href="{{ route('coordenacao.tarefas.edit', $tarefa) }}" class="botao botao-secundario">Editar</a>
                                <button type="button" class="botao botao-perigo"
                                    data-excluir
                                    data-url="{{ route('coordenacao.tarefas.destroy', $tarefa) }}"
                                    data-nome="{{ $tarefa->quando }} · {{ \Illuminate\Support\Str::limit($tarefa->descricao, 80) }}">Excluir</button>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        @endif
    </main>
</div>

@include('partials.popup-excluir')
@endsection
