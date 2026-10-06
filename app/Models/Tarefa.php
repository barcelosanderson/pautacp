<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tarefa extends Model
{
    use HasFactory;

    protected $fillable = [
        'escola_id',
        'quando',
        'inicio',
        'prazo',
        'descricao',
        'repete_todo_mes',
        'checavel',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'date',
            'prazo' => 'date',
            'repete_todo_mes' => 'boolean',
            'checavel' => 'boolean',
        ];
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    public function concluidaPor(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Status mostrado na linha da pauta.
     * "chave" vira a classe de cor (status-feito, status-logo...), "rotulo" é o texto.
     *
     * @return array{chave: string, rotulo: string}
     */
    public function status(bool $feita, CarbonInterface $hoje): array
    {
        if ($feita) {
            return ['chave' => 'feito', 'rotulo' => 'Feito'];
        }

        $dias = (int) round($hoje->copy()->startOfDay()->diffInDays($this->prazo->copy()->startOfDay(), false));

        return match (true) {
            $dias < 0 => ['chave' => 'vencido', 'rotulo' => 'Vencido'],
            $dias === 0 => ['chave' => 'logo', 'rotulo' => 'Vence hoje'],
            $dias === 1 => ['chave' => 'logo', 'rotulo' => 'Vence amanhã'],
            $dias === 2 => ['chave' => 'logo', 'rotulo' => 'Vence em 2 dias'],
            default => ['chave' => 'afazer', 'rotulo' => 'A fazer'],
        };
    }
}
