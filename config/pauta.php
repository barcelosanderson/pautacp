<?php

return [

    /*
     * Escola criada pelo comando "php artisan migrate --seed".
     * O código é o que a coordenação entrega pessoalmente para cada professor.
     * Se ESCOLA_CODIGO ficar vazio, um código é sorteado e mostrado na tela.
     */
    'escola' => [
        'nome' => env('ESCOLA_NOME', 'Minha escola'),
        'codigo' => env('ESCOLA_CODIGO'),
    ],

    // Ano das datas da pauta (as datas da tabela vêm sem ano).
    'ano' => (int) env('PAUTA_ANO', 2026),

];
