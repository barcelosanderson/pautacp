<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\Tarefa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Tarefa>
 */
class TarefaFactory extends Factory
{
    public function definition(): array
    {
        $data = Carbon::create(2026, 10, 15);

        return [
            'escola_id' => Escola::factory(),
            'quando' => $data->format('d/m'),
            'inicio' => $data,
            'prazo' => $data,
            'descricao' => fake()->sentence(),
            'repete_todo_mes' => false,
            'checavel' => true,
            'ordem' => 0,
        ];
    }

    /** Tarefa de um dia só, na data informada ("2026-10-09"). */
    public function noDia(string $data): static
    {
        $dia = Carbon::parse($data);

        return $this->state(fn () => [
            'quando' => $dia->format('d/m'),
            'inicio' => $dia,
            'prazo' => $dia,
        ]);
    }
}
