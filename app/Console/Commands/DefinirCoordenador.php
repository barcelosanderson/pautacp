<?php

namespace App\Console\Commands;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Exemplo: php artisan pauta:coordenador "Maria" "Souza"
 * Se a pessoa já tem conta, ela vira coordenador(a). Se não tem, a conta é criada.
 */
class DefinirCoordenador extends Command
{
    protected $signature = 'pauta:coordenador
        {nome : Primeiro nome}
        {sobrenome : Sobrenome}
        {--codigo= : Código da escola (só é preciso se houver mais de uma escola)}';

    protected $description = 'Cria ou promove uma pessoa a coordenador(a) da escola';

    public function handle(): int
    {
        $escola = $this->escola();
        if (! $escola) {
            return self::FAILURE;
        }

        $nome = (string) $this->argument('nome');
        $sobrenome = (string) $this->argument('sobrenome');

        $usuario = $escola->usuarios()->where('chave', User::chaveDe($nome, $sobrenome))->first()
            ?? $escola->usuarios()->make(['nome' => $nome, 'sobrenome' => $sobrenome]);

        $jaExistia = $usuario->exists;
        $usuario->papel = User::PAPEL_COORDENADOR;
        $usuario->save();

        $this->info(($jaExistia ? 'Conta promovida' : 'Conta criada')
            ." como coordenador(a): {$usuario->nome_completo} ({$escola->nome}).");

        return self::SUCCESS;
    }

    private function escola(): ?Escola
    {
        $codigo = $this->option('codigo');

        if ($codigo) {
            $escola = Escola::porCodigo($codigo);
            if (! $escola) {
                $this->error("Nenhuma escola com o código {$codigo}.");
            }

            return $escola;
        }

        $escolas = Escola::all();

        if ($escolas->count() === 1) {
            return $escolas->first();
        }

        $this->error($escolas->isEmpty()
            ? 'Nenhuma escola cadastrada. Rode antes: php artisan migrate --seed'
            : 'Há mais de uma escola. Informe qual com --codigo=CODIGO');

        return null;
    }
}
