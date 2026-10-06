<?php

namespace Database\Factories;

use App\Models\Escola;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Escola>
 */
class EscolaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => 'Escola '.fake()->lastName(),
            'codigo' => Str::upper(Str::random(6)),
        ];
    }
}
