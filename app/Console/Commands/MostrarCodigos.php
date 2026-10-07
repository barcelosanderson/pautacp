<?php

namespace App\Console\Commands;

use App\Models\Escola;
use Illuminate\Console\Command;

/**
 * Exemplo: php artisan pauta:codigos
 * Mostra o código dos professores e o código da coordenação de cada escola.
 */
class MostrarCodigos extends Command
{
    protected $signature = 'pauta:codigos';

    protected $description = 'Mostra os códigos de acesso (professores e coordenação)';

    public function handle(): int
    {
        $escolas = Escola::orderBy('nome')->get();

        if ($escolas->isEmpty()) {
            $this->error('Nenhuma escola cadastrada. Rode antes: php artisan migrate --seed');

            return self::FAILURE;
        }

        $this->table(
            ['Escola', 'Código dos professores', 'Código da coordenação'],
            $escolas->map(fn (Escola $escola) => [$escola->nome, $escola->codigo, $escola->codigo_coordenacao]),
        );

        $this->line('Não passe o código da coordenação para os professores: com ele dá para editar a pauta.');

        return self::SUCCESS;
    }
}
