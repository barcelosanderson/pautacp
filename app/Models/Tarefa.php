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
     * Texto da data montado a partir do primeiro e do último dia:
     * "06/10", "06 e 07/10", "06 a 09/10" ou "21/12 a 17/01".
     */
    public static function rotuloDasDatas(CarbonInterface $inicio, CarbonInterface $prazo): string
    {
        if ($inicio->isSameDay($prazo)) {
            return $inicio->format('d/m');
        }

        if ($inicio->format('Y-m') === $prazo->format('Y-m')) {
            $ligacao = $inicio->copy()->addDay()->isSameDay($prazo) ? ' e ' : ' a ';

            return $inicio->format('d').$ligacao.$prazo->format('d/m');
        }

        return $inicio->format('d/m').' a '.$prazo->format('d/m');
    }

    /** O texto da data só quando foi escrito à mão (ex.: "Até 06/10"); vazio se é o automático. */
    public function quandoPersonalizado(): string
    {
        return $this->quando === static::rotuloDasDatas($this->inicio, $this->prazo) ? '' : $this->quando;
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
