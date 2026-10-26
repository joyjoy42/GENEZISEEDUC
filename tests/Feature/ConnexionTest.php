<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_formulaire_de_connexion_est_accessible(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Connexion');
    }

    public function test_un_compte_valide_ouvre_le_back_office(): void
    {
        $membre = User::factory()->create([
            'email' => 'joy@genezise.test',
            'password' => Hash::make('motdepasse-solide-2026'),
        ]);

        $this->post(route('connexion'), [
            'email' => 'joy@genezise.test',
            'password' => 'motdepasse-solide-2026',
        ])->assertRedirect(route('admin.tableau-bord'));

        $this->assertAuthenticatedAs($membre);
    }

    public function test_un_mauvais_mot_de_passe_est_refuse(): void
    {
        User::factory()->create([
            'email' => 'joy@genezise.test',
            'password' => Hash::make('motdepasse-solide-2026'),
        ]);

        $this->post(route('connexion'), [
            'email' => 'joy@genezise.test',
            'password' => 'mauvais',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_la_deconnexion_ferme_la_session(): void
    {
        $membre = User::factory()->create();

        $this->actingAs($membre)
            ->post(route('deconnexion'))
            ->assertRedirect(route('accueil'));

        $this->assertGuest();
    }

    /**
     * Le formulaire de connexion est la seule porte du back-office : sans
     * limite, on peut essayer des mots de passe indéfiniment.
     */
    public function test_les_tentatives_de_connexion_sont_limitees(): void
    {
        User::factory()->create([
            'email' => 'joy@genezise.test',
            'password' => Hash::make('motdepasse-solide-2026'),
        ]);

        for ($essai = 0; $essai < 5; $essai++) {
            $this->post(route('connexion'), [
                'email' => 'joy@genezise.test',
                'password' => 'mauvais',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('connexion'), [
            'email' => 'joy@genezise.test',
            'password' => 'motdepasse-solide-2026',
        ])->assertStatus(429);

        $this->assertGuest();
    }

    public function test_aucune_route_dinscription_nexiste(): void
    {
        $this->post('/register', [
            'name' => 'Intrus',
            'email' => 'intrus@example.com',
            'password' => 'motdepasse-solide-2026',
        ])->assertNotFound();
    }
}
