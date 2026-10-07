<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Fileira de botões "Outubro, Novembro, ..., Todos os meses".
 * Abre no mês que a pessoa escolheu; senão no mês atual; senão em todos.
 */
class FiltroDeMes
{
    public readonly string $escolhido;

    /**
     * @param  Collection<string, object{chave: string, nome: string}>  $meses  chave "2026-10"
     * @param  mixed  $pedido  valor de ?mes= (pode vir qualquer coisa da URL)
     * @param  string  $rota  rota que recebe ?mes=
     */
    public function __construct(
        private readonly Collection $meses,
        mixed $pedido,
        CarbonInterface $hoje,
        private readonly string $rota,
    ) {
        $atual = $hoje->format('Y-m');

        $this->escolhido = match (true) {
            $pedido === 'todos' => 'todos',
            is_string($pedido) && $meses->has($pedido) => $pedido,
            $meses->has($atual) => $atual,
            default => 'todos',
        };
    }

    public function visiveis(): Collection
    {
        return $this->escolhido === 'todos' ? $this->meses : $this->meses->only([$this->escolhido]);
    }

    /** @return Collection<int, array{rotulo: string, url: string, ativo: bool}> */
    public function botoes(): Collection
    {
        return $this->meses
            ->map(fn (object $mes) => [
                'rotulo' => $mes->nome,
                'url' => route($this->rota, ['mes' => $mes->chave]),
                'ativo' => $this->escolhido === $mes->chave,
            ])
            ->values()
            ->push([
                'rotulo' => 'Todos os meses',
                'url' => route($this->rota, ['mes' => 'todos']),
                'ativo' => $this->escolhido === 'todos',
            ]);
    }
}
