<?php

namespace Database\Factories;

use App\Models\Question;
use App\Orientation\Dimension;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'libelle' => fake()->unique()->sentence(),
            'dimension' => fake()->randomElement(Dimension::cases())->value,
            'ordre' => fake()->numberBetween(1, 100),
            'est_active' => true,
        ];
    }

    public function pour(Dimension $dimension): static
    {
        return $this->state(fn () => ['dimension' => $dimension->value]);
    }

    public function retiree(): static
    {
        return $this->state(fn () => ['est_active' => false]);
    }
}
