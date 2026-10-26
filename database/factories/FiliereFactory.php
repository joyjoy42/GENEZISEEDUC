<?php

namespace Database\Factories;

use App\Models\Filiere;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Filiere>
 */
class FiliereFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->unique()->words(3, true);

        return [
            'nom' => Str::ucfirst($nom),
            'slug' => Str::slug($nom),
            'niveau' => fake()->randomElement(['CAP / CQP', 'Licence', 'Licence puis Master']),
            'duree_annees' => fake()->numberBetween(2, 5),
            'description' => fake()->sentence(),
            'etablissements' => fake()->company(),
            'formation_disponible_pays' => true,
            'financements' => fake()->sentence(),
        ];
    }
}
