<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Escola extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'codigo', 'codigo_coordenacao'];

    /**
     * O código é comparado sem espaços e em maiúsculas,
     * então "ab 12c" e "AB12C" são o mesmo código.
     */
    public static function normalizarCodigo(string $codigo): string
    {
        return Str::upper((string) preg_replace('/\s+/', '', $codigo));
    }

    /** Escola pelo código dos professores. */
    public static function porCodigo(string $codigo): ?self
    {
        return static::where('codigo', static::normalizarCodigo($codigo))->first();
    }

    /**
     * Descobre a escola e o tipo de conta a partir do código digitado:
     * o código dos professores abre conta de professor,
     * o código da coordenação abre conta da coordenação.
     *
     * @return array{0: self, 1: string}|null [escola, papel]
     */
    public static function acessoPorCodigo(string $codigo): ?array
    {
        $codigo = static::normalizarCodigo($codigo);
        if ($codigo === '') {
            return null;
        }

        if ($escola = static::where('codigo', $codigo)->first()) {
            return [$escola, User::PAPEL_PROFESSOR];
        }

        if ($escola = static::where('codigo_coordenacao', $codigo)->first()) {
            return [$escola, User::PAPEL_COORDENADOR];
        }

        return null;
    }

    /** Se o código já é usado por alguma escola (em qualquer um dos dois campos). */
    public static function codigoEmUso(string $codigo, ?int $ignorarEscolaId = null): bool
    {
        $codigo = static::normalizarCodigo($codigo);

        return static::query()
            ->when($ignorarEscolaId, fn ($consulta) => $consulta->whereKeyNot($ignorarEscolaId))
            ->where(fn ($consulta) => $consulta->where('codigo', $codigo)->orWhere('codigo_coordenacao', $codigo))
            ->exists();
    }

    /** Código novo, sem letras e números que se confundem (O e 0, I e 1). */
    public static function sortearCodigo(int $tamanho = 6): string
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $codigo = '';
            for ($i = 0; $i < $tamanho; $i++) {
                $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
            }
        } while (static::codigoEmUso($codigo));

        return $codigo;
    }

    protected function codigo(): Attribute
    {
        return Attribute::make(set: fn (string $valor) => static::normalizarCodigo($valor));
    }

    protected function codigoCoordenacao(): Attribute
    {
        return Attribute::make(set: fn (?string $valor) => $valor === null ? null : static::normalizarCodigo($valor));
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class);
    }
}
