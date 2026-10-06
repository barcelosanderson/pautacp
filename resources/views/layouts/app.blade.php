<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') · {{ config('app.name') }}</title>
    @include('partials.recursos')
</head>
<body>
    @include('partials.avisos')
    @yield('conteudo')
</body>
</html>
