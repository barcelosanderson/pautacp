{{-- Ícones de traço, na cor do texto. Uso: <x-icone nome="certo" tamanho="22" /> --}}
@props(['nome', 'tamanho' => 22, 'espessura' => null])
@php
    $desenhos = [
        'certo' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'alerta' => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><path d="M12 16.5v.5"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'fechar' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'sair' => '<path d="M9 20H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'pessoa' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
        'engrenagem' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    ];
    $espessuras = ['certo' => 3, 'alerta' => 2.6, 'menu' => 2.5, 'fechar' => 2.5];
@endphp
<svg width="{{ $tamanho }}" height="{{ $tamanho }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $espessura ?? ($espessuras[$nome] ?? 2.2) }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes }}>{!! $desenhos[$nome] ?? '' !!}</svg>
