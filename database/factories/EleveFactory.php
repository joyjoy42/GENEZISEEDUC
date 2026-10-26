<?php

namespace Database\Factories;

use App\Models\Eleve;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Eleve>
 */
class EleveFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supabase_id' => (string) Str::uuid(),
            'email' => fake()->unique()->safeEmail(),
            'annee_naissance' => (int) date('Y') - fake()->numberBetween(15, 30),
        ];
    }
}
