<?php

namespace Database\Factories;

use App\Models\Metier;
use App\Models\Secteur;
use App\Orientation\Profil;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Metier>
 */
class MetierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->unique()->words(3, true);

        return [
            'secteur_id' => Secteur::factory(),
            'nom' => Str::ucfirst($nom),
            'slug' => Str::slug($nom),
            'accroche' => fake()->sentence(),
            'le_quotidien' => fake()->paragraph(),
            'competences' => fake()->words(4),
            'outils' => fake()->words(3),
            'salaire_min' => null,
            'salaire_max' => null,
            'ou_travailler' => fake()->sentence(),
            'debouches' => fake()->sentence(),
            'idee_fausse' => fake()->sentence(),
            'realite' => fake()->paragraph(),
            'niveau_etude' => 'Licence',
            'est_publie' => true,
            'publie_le' => now(),
        ];
    }

    /**
     * Un métier encore en brouillon, invisible pour le public.
     */
    public function brouillon(): static
    {
        return $this->state(fn () => [
            'est_publie' => false,
            'publie_le' => null,
        ]);
    }

    /**
     * Un métier dont le profil RIASEC est renseigné, donc recommandable.
     *
     * @param  array<string, int>  $scores
     */
    public function profil(array $scores): static
    {
        return $this->state(fn () => Profil::depuisTableau($scores)->enColonnes());
    }

    /**
     * Un métier dont la fourchette de salaire a été mesurée en interview.
     */
    public function avecSalaire(int $min, int $max): static
    {
        return $this->state(fn () => [
            'salaire_min' => $min,
            'salaire_max' => $max,
        ]);
    }
}
