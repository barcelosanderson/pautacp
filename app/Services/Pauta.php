<?php

namespace App\Services;

use App\Models\Tarefa;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Monta a pauta de uma pessoa: as tarefas da escola dela,
 * o que já foi feito, a cor de cada linha e o resumo do topo.
 */
class Pauta
{
    private const MESES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    private ?Collection $itens = null;

    /** "2026-10" vira "Outubro". */
    public static function nomeDoMes(string $chave): string
    {
        return self::MESES[(int) substr($chave, 5, 2)];
    }

    public function __construct(
        private readonly User $usuario,
        private readonly CarbonInterface $hoje,
    ) {}

    /**
     * @return Collection<int, object{tarefa: Tarefa, feita: bool, status: ?array}>
     */
    public function itens(): Collection
    {
        if ($this->itens !== null) {
            return $this->itens;
        }

        $feitas = $this->usuario->tarefasFeitas()->pluck('tarefas.id')->flip();

        return $this->itens = $this->usuario->escola->tarefas()
            ->orderBy('inicio')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get()
            ->map(function (Tarefa $tarefa) use ($feitas) {
                $feita = $feitas->has($tarefa->id);

                return (object) [
                    'tarefa' => $tarefa,
                    'feita' => $feita,
                    'status' => $tarefa->checavel ? $tarefa->status($feita, $this->hoje) : null,
                ];
            });
    }

    /**
     * Tarefas separadas por mês, na ordem do calendário. A chave é "2026-10".
     *
     * @return Collection<string, object{chave: string, nome: string, itens: Collection, total: int, feitas: int}>
     */
    public function meses(): Collection
    {
        return $this->itens()
            ->groupBy(fn (object $item) => $item->tarefa->inicio->format('Y-m'))
            ->map(fn (Collection $itens, string $chave) => (object) [
                'chave' => $chave,
                'nome' => self::nomeDoMes($chave),
                'itens' => $itens,
                'total' => $itens->filter(fn (object $item) => $item->tarefa->checavel)->count(),
                'feitas' => $itens->where('feita', true)->count(),
            ]);
    }

    /**
     * @return array{feitas: int, total: int, percentual: int, texto: string, alerta: ?string}
     */
    public function resumo(): array
    {
        $checaveis = $this->itens()->filter(fn (object $item) => $item->tarefa->checavel);

        $total = $checaveis->count();
        $feitas = $checaveis->where('feita', true)->count();
        $vencidas = $checaveis->where('status.chave', 'vencido')->count();
        $logo = $checaveis->where('status.chave', 'logo')->count();

        $partes = [];
        if ($vencidas > 0) {
            $partes[] = $vencidas === 1 ? '1 tarefa vencida' : "{$vencidas} tarefas vencidas";
        }
        if ($logo > 0) {
            $partes[] = ($logo === 1 ? '1 tarefa que vence' : "{$logo} tarefas que vencem").' em até 2 dias';
        }

        return [
            'feitas' => $feitas,
            'total' => $total,
            'percentual' => $total > 0 ? (int) round($feitas * 100 / $total) : 0,
            'texto' => "Você já fez {$feitas} de {$total} ".($total === 1 ? 'tarefa' : 'tarefas').'.',
            'alerta' => $partes ? 'Atenção: '.implode(' e ', $partes).'.' : null,
        ];
    }
}
