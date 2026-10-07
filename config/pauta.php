<?php

return [

    /*
     * Escola criada pelo comando "php artisan migrate --seed".
     * - codigo: entregue pessoalmente aos professores;
     * - codigo_coordenacao: só para quem edita a pauta. Não passe para os professores.
     * Códigos vazios são sorteados e mostrados na tela. Depois da instalação,
     * os códigos ficam no banco: veja com "php artisan pauta:codigos"
     * ou na tela "Códigos de acesso".
     */
    'escola' => [
        'nome' => env('ESCOLA_NOME', 'Minha escola'),
        'codigo' => env('ESCOLA_CODIGO'),
        'codigo_coordenacao' => env('ESCOLA_CODIGO_COORDENACAO'),
    ],

    // Ano das datas da pauta (as datas da tabela vêm sem ano).
    'ano' => (int) env('PAUTA_ANO', 2026),

];
