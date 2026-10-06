<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'escola_id' => Escola::factory(),
            'nome' => fake()->firstName(),
            'sobrenome' => fake()->lastName(),
            'papel' => User::PAPEL_PROFESSOR,
            'remember_token' => Str::random(10),
        ];
    }

    public function coordenador(): static
    {
        return $this->state(fn () => ['papel' => User::PAPEL_COORDENADOR]);
    }
}
