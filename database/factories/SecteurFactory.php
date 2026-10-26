<?php

namespace Database\Factories;

use App\Models\Secteur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Secteur>
 */
class SecteurFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->unique()->words(2, true);

        return [
            'nom' => Str::ucfirst($nom),
            'slug' => Str::slug($nom),
            'description' => fake()->sentence(),
            'couleur' => fake()->hexColor(),
            'ordre' => fake()->numberBetween(0, 10),
        ];
    }
}
