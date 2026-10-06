<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const PAPEL_PROFESSOR = 'professor';

    public const PAPEL_COORDENADOR = 'coordenador';

    protected $fillable = ['escola_id', 'nome', 'sobrenome', 'papel'];

    protected $hidden = ['remember_token'];

    protected static function booted(): void
    {
        static::saving(function (User $usuario) {
            $usuario->nome = Str::squish($usuario->nome);
            $usuario->sobrenome = Str::squish($usuario->sobrenome);
            $usuario->chave = static::chaveDe($usuario->nome, $usuario->sobrenome);
        });
    }

    /**
     * Nome completo sem acentos, em minúsculas e sem espaços sobrando.
     * "José  da Silva" e "jose da silva" geram a mesma chave.
     */
    public static function chaveDe(string $nome, string $sobrenome): string
    {
        return (string) Str::of($nome.' '.$sobrenome)->squish()->ascii()->lower();
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    public function tarefasFeitas(): BelongsToMany
    {
        return $this->belongsToMany(Tarefa::class)->withTimestamps();
    }

    public function ehCoordenador(): bool
    {
        return $this->papel === self::PAPEL_COORDENADOR;
    }

    protected function nomeCompleto(): Attribute
    {
        return Attribute::get(fn () => $this->nome.' '.$this->sobrenome);
    }

    /** "Maria" + "da Silva" vira "MS" (primeira letra do nome e da última palavra do sobrenome). */
    protected function iniciais(): Attribute
    {
        return Attribute::get(fn () => Str::upper(
            mb_substr($this->nome, 0, 1).mb_substr((string) Str::of($this->sobrenome)->squish()->afterLast(' '), 0, 1)
        ));
    }
}
