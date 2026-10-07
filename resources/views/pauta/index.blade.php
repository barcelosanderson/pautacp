@extends('layouts.app')

@section('titulo', 'Minha pauta')

@section('conteudo')
<div class="pagina">
    @include('partials.menu')

    <main class="pilha pilha-pauta">
        <section class="card">
            <p class="texto-secundario">Hoje é {{ $hoje->locale('pt_BR')->translatedFormat('l, d/m') }}</p>
            <h1 class="titulo-pagina">Olá, {{ auth()->user()->nome }}. Esta é a sua pauta.</h1>
            <p class="texto">Marque cada tarefa assim que terminar. Marcou por engano? É só clicar de novo para desmarcar.</p>

            <div class="pilha-pequena">
                <div class="progresso-legenda">
                    <span data-progresso-texto>{{ $resumo['texto'] }}</span>
                    <span data-progresso-percentual>{{ $resumo['percentual'] }}%</span>
                </div>
                <div class="progresso" data-progresso role="progressbar" aria-label="Tarefas feitas"
                    aria-valuemin="0" aria-valuemax="{{ $resumo['total'] }}" aria-valuenow="{{ $resumo['feitas'] }}">
                    <div class="progresso-barra" data-progresso-barra style="width: {{ $resumo['percentual'] }}%"
                        @if ($resumo['percentual'] === 0) data-vazia @endif></div>
                </div>
            </div>
        </section>

        <div class="recado" data-recado role="note" @if (! $resumo['alerta']) hidden @endif>
            <x-icone nome="alerta" tamanho="26" espessura="2.4" />
            <span data-recado-texto>{{ $resumo['alerta'] }}</span>
        </div>

        @if ($vazia)
            <section class="card vazio">
                <h2 class="titulo-card">Nenhuma tarefa criada ainda.</h2>
                <p class="texto">Quando a pauta for publicada, as tarefas aparecem aqui. Dúvidas? Fale com o cara.</p>
            </section>
        @else
            <nav class="filtros" aria-label="Escolha o mês">
                @foreach ($filtros as $filtro)
                    <a href="{{ $filtro['url'] }}" class="botao" @if ($filtro['ativo']) aria-current="page" @endif>{{ $filtro['rotulo'] }}</a>
                @endforeach
            </nav>

            @foreach ($meses as $mes)
                <section class="mes" data-mes aria-labelledby="mes-{{ $mes->chave }}">
                    <div class="mes-cabecalho">
                        <h2 class="titulo-pagina" id="mes-{{ $mes->chave }}">{{ $mes->nome }}</h2>
                        <span class="texto-secundario" data-mes-contagem>
                            {{ $mes->total }} {{ $mes->total === 1 ? 'tarefa' : 'tarefas' }} · {{ $mes->feitas }} {{ $mes->feitas === 1 ? 'feita' : 'feitas' }}
                        </span>
                    </div>

                    @foreach ($mes->itens as $item)
                        @if ($item->tarefa->checavel)
                            <label for="tarefa-{{ $item->tarefa->id }}" class="tarefa status-{{ $item->status['chave'] }}"
                                data-tarefa data-url="{{ route('tarefas.marcar', $item->tarefa) }}">
                                <input id="tarefa-{{ $item->tarefa->id }}" class="caixa" type="checkbox" @checked($item->feita)>
                                <span class="caixa-visual caixa-visual-grande">
                                    <x-icone nome="certo" tamanho="18" espessura="3.5" />
                                </span>
                                <span class="tarefa-corpo">
                                    <span class="tarefa-topo">
                                        <span class="tarefa-data">{{ $item->tarefa->quando }}</span>
                                        <span class="selo" data-selo>{{ $item->status['rotulo'] }}</span>
                                        @if ($item->tarefa->repete_todo_mes)
                                            <span class="selo selo-suave">Repete todo mês</span>
                                        @endif
                                    </span>
                                    <span class="tarefa-texto">{{ $item->tarefa->descricao }}</span>
                                </span>
                            </label>
                        @else
                            <div class="informativo">
                                <p class="texto-secundario">{{ $item->tarefa->quando }}</p>
                                <h3 class="titulo-card">{{ $item->tarefa->descricao }}</h3>
                            </div>
                        @endif
                    @endforeach
                </section>
            @endforeach
        @endif
    </main>
</div>
@endsection
