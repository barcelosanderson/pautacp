{{-- Base das páginas de erro: não depende de sessão nem de login. --}}
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') · {{ config('app.name') }}</title>
    @include('partials.recursos')
</head>
<body>
    <main class="pagina pagina-estreita">
        <span class="marca">{{ config('app.name') }}</span>
        <section class="card card-centro">
            <h1 class="titulo-pagina">@yield('titulo')</h1>
            <p class="texto">@yield('mensagem')</p>
            <a href="{{ url('/') }}" class="botao botao-principal">Voltar para o início</a>
        </section>
    </main>
</body>
</html>
