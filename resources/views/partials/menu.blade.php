{{-- Menu flutuante: preso no topo. Abaixo de 900px vira o botão "Menu" com painel. --}}
@php
    $usuario = auth()->user();
    $abas = [
        ['rotulo' => 'Minha pauta', 'url' => route('pauta'), 'ativa' => request()->routeIs('pauta')],
    ];
    if ($usuario->ehCoordenador()) {
        $abas[] = ['rotulo' => 'Editar pauta', 'url' => route('coordenacao.tarefas.index'), 'ativa' => request()->routeIs('coordenacao.tarefas.*')];
        $abas[] = ['rotulo' => 'Códigos de acesso', 'url' => route('coordenacao.codigos'), 'ativa' => request()->routeIs('coordenacao.codigos*')];
    }
@endphp
<nav class="menu" data-menu aria-label="Menu principal">
    <div class="menu-barra">
        <span class="avatar" title="{{ $usuario->nome_completo }}" aria-hidden="true">{{ $usuario->iniciais }}</span>

        <div class="menu-abas">
            @foreach ($abas as $aba)
                <a href="{{ $aba['url'] }}" class="botao botao-secundario" @if ($aba['ativa']) aria-current="page" @endif>{{ $aba['rotulo'] }}</a>
            @endforeach
        </div>

        <div class="menu-direita">
            <form method="post" action="{{ route('sair') }}">
                @csrf
                <button type="submit" class="botao botao-secundario">
                    <x-icone nome="sair" />
                    <span>Sair</span>
                </button>
            </form>
        </div>

        <button type="button" class="botao botao-secundario menu-abrir" data-menu-abrir aria-expanded="false" aria-controls="menu-painel">
            <x-icone nome="menu" tamanho="24" />
            <span data-menu-rotulo>Menu</span>
        </button>
    </div>

    <div class="menu-painel" id="menu-painel">
        @foreach ($abas as $aba)
            <a href="{{ $aba['url'] }}" class="botao botao-secundario" @if ($aba['ativa']) aria-current="page" @endif>{{ $aba['rotulo'] }}</a>
        @endforeach
        <form method="post" action="{{ route('sair') }}">
            @csrf
            <button type="submit" class="botao botao-secundario">
                <x-icone nome="sair" />
                <span>Sair</span>
            </button>
        </form>
    </div>
</nav>
