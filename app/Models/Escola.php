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

    protected $fillable = ['nome', 'codigo'];

    /**
     * O código é comparado sem espaços e em maiúsculas,
     * então "ab 12c" e "AB12C" são o mesmo código.
     */
    public static function normalizarCodigo(string $codigo): string
    {
        return Str::upper((string) preg_replace('/\s+/', '', $codigo));
    }

    public static function porCodigo(string $codigo): ?self
    {
        return static::where('codigo', static::normalizarCodigo($codigo))->first();
    }

    protected function codigo(): Attribute
    {
        return Attribute::make(set: fn (string $valor) => static::normalizarCodigo($valor));
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
