<?php

namespace Database\Factories;

use App\Models\Metier;
use App\Models\Temoignage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Temoignage>
 */
class TemoignageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'metier_id' => Metier::factory(),
            'nom' => fake()->name(),
            'fonction' => fake()->randomElement(['Patron de garage', 'Couturier', 'Psychologue clinicien', 'Développeur back-end']),
            'entreprise' => fake()->company(),
            'ville' => 'Cotonou',
            'citation' => fake()->sentence(),
            'video_url' => fake()->url(),
            'plateforme' => fake()->randomElement(['tiktok', 'instagram', 'youtube']),
            'duree_secondes' => fake()->numberBetween(30, 180),
            'date_interview' => fake()->dateTimeBetween('-6 months'),
            'est_publie' => true,
        ];
    }

    /**
     * Une interview tournée mais pas encore montée ni publiée.
     */
    public function brouillon(): static
    {
        return $this->state(fn () => ['est_publie' => false]);
    }
}
