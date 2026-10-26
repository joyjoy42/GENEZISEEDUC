<?php

namespace Database\Factories;

use App\Models\ResultatTest;
use App\Orientation\Dimension;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ResultatTest>
 */
class ResultatTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scores = [];

        foreach (Dimension::cases() as $dimension) {
            $scores[$dimension->colonne()] = fake()->numberBetween(0, 20);
        }

        return [
            'jeton' => (string) Str::ulid(),
            'nombre_reponses' => 24,
            ...$scores,
        ];
    }
}
