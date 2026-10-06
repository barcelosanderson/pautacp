{{-- Menu flutuante: preso no topo. Abaixo de 900px vira o botão "Menu" com painel. --}}
@php($usuario = auth()->user())
<nav class="menu" data-menu aria-label="Menu principal">
    <div class="menu-barra">
        <span class="avatar" title="{{ $usuario->nome_completo }}" aria-hidden="true">{{ $usuario->iniciais }}</span>

        <div class="menu-abas">
            <a href="{{ route('pauta') }}" class="botao botao-secundario" @if (request()->routeIs('pauta')) aria-current="page" @endif>Minha pauta</a>
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
        <a href="{{ route('pauta') }}" class="botao botao-secundario" @if (request()->routeIs('pauta')) aria-current="page" @endif>Minha pauta</a>
        <form method="post" action="{{ route('sair') }}">
            @csrf
            <button type="submit" class="botao botao-secundario">
                <x-icone nome="sair" />
                <span>Sair</span>
            </button>
        </form>
    </div>
</nav>
