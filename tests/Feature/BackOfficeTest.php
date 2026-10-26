<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Secteur;
use App\Models\Temoignage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackOfficeTest extends TestCase
{
    use RefreshDatabase;

    private function membre(): User
    {
        return User::factory()->create();
    }

    public function test_le_back_office_est_ferme_aux_visiteurs(): void
    {
        $metier = Metier::factory()->create();

        $this->get(route('admin.tableau-bord'))->assertRedirect(route('login'));
        $this->get(route('admin.metiers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.metiers.edit', $metier))->assertRedirect(route('login'));
        $this->post(route('admin.metiers.store'), [])->assertRedirect(route('login'));
        $this->delete(route('admin.metiers.destroy', $metier))->assertRedirect(route('login'));

        $this->assertDatabaseHas('metiers', ['id' => $metier->id]);
    }

    public function test_le_tableau_de_bord_signale_les_metiers_sans_interview(): void
    {
        $avec = Metier::factory()->create(['nom' => 'Couturier']);
        Temoignage::factory()->for($avec, 'metier')->create();

        Metier::factory()->create(['nom' => 'Psychiatre']);

        $this->actingAs($this->membre())
            ->get(route('admin.tableau-bord'))
            ->assertOk()
            ->assertSee('Métiers publiés sans aucune interview')
            ->assertSee('Psychiatre');
    }

    public function test_un_membre_cree_un_metier(): void
    {
        $secteur = Secteur::factory()->create();
        $filiere = Filiere::factory()->create();

        $this->actingAs($this->membre())
            ->post(route('admin.metiers.store'), [
                'secteur_id' => $secteur->id,
                'nom' => 'Soudeur',
                'slug' => '',
                'accroche' => 'Il assemble le métal au millimètre.',
                'le_quotidien' => 'Des journées en atelier, masque sur le visage.',
                'competences' => "Lecture de plan\nSoudure à l'arc\n\n",
                'outils' => "Poste à souder\nMeuleuse",
                'idee_fausse' => "« C'est un métier sans avenir. »",
                'realite' => 'La demande dépasse le nombre de soudeurs formés.',
                'niveau_etude' => 'CAP',
                'filieres' => [$filiere->id],
                'est_publie' => '1',
            ])
            ->assertRedirect(route('admin.metiers.index'));

        $metier = Metier::where('slug', 'soudeur')->sole();

        $this->assertSame('Soudeur', $metier->nom);
        $this->assertTrue($metier->est_publie);
        $this->assertNotNull($metier->publie_le);
        $this->assertSame(['Lecture de plan', "Soudure à l'arc"], $metier->competences);
        $this->assertSame(['Poste à souder', 'Meuleuse'], $metier->outils);
        $this->assertTrue($metier->filieres->contains($filiere));
    }

    public function test_un_membre_cree_une_filiere_sans_formation_au_pays(): void
    {
        $this->actingAs($this->membre())
            ->post(route('admin.filieres.store'), [
                'nom' => 'Science informatique',
                'slug' => '',
                'niveau' => 'Licence puis Master',
                'duree_annees' => 5,
                'description' => 'Le raisonnement derrière la machine.',
                'etablissements' => 'Aucune filière de ce nom dans le guide du ministère.',
                'formation_disponible_pays' => '0',
                'financements' => 'Bourses de coopération, vérifié en octobre 2026.',
            ])
            ->assertRedirect(route('admin.filieres.index'));

        $filiere = Filiere::where('slug', 'science-informatique')->sole();

        $this->assertFalse($filiere->formation_disponible_pays);
        $this->assertSame('Bourses de coopération, vérifié en octobre 2026.', $filiere->financements);
    }

    /**
     * Le formulaire n'oblige pas à remplir les financements, mais une filière
     * créée sans rien dire reste présumée disponible dans le pays.
     */
    public function test_une_filiere_creee_sans_financements_reste_disponible_au_pays(): void
    {
        $this->actingAs($this->membre())
            ->post(route('admin.filieres.store'), [
                'nom' => 'Génie informatique',
                'slug' => '',
                'formation_disponible_pays' => '1',
            ])
            ->assertRedirect(route('admin.filieres.index'));

        $filiere = Filiere::where('slug', 'genie-informatique')->sole();

        $this->assertTrue($filiere->formation_disponible_pays);
        $this->assertNull($filiere->financements);
    }

    public function test_un_metier_sans_accroche_est_refuse(): void
    {
        $secteur = Secteur::factory()->create();

        $this->actingAs($this->membre())
            ->post(route('admin.metiers.store'), [
                'secteur_id' => $secteur->id,
                'nom' => 'Soudeur',
                'accroche' => '',
            ])
            ->assertSessionHasErrors('accroche');

        $this->assertDatabaseMissing('metiers', ['nom' => 'Soudeur']);
    }

    /**
     * Laravel ne livre que l'anglais : sans les fichiers de langue, une
     * application entièrement française répond « The accroche field is
     * required. » à ses utilisateurs.
     */
    public function test_les_messages_de_validation_sont_en_francais(): void
    {
        $this->actingAs($this->membre())
            ->post(route('admin.metiers.store'), ['nom' => ''])
            ->assertSessionHasErrors([
                'nom' => 'Le champ nom du métier est obligatoire.',
                'secteur_id' => 'Le champ secteur est obligatoire.',
                'accroche' => 'Le champ accroche est obligatoire.',
            ]);
    }

    public function test_un_salaire_maximum_inferieur_au_minimum_est_refuse(): void
    {
        $secteur = Secteur::factory()->create();

        $this->actingAs($this->membre())
            ->post(route('admin.metiers.store'), [
                'secteur_id' => $secteur->id,
                'nom' => 'Soudeur',
                'accroche' => 'Il assemble le métal.',
                'salaire_min' => 150000,
                'salaire_max' => 60000,
            ])
            ->assertSessionHasErrors('salaire_max');
    }

    public function test_repasser_un_metier_en_brouillon_efface_sa_date_de_publication(): void
    {
        $metier = Metier::factory()->create();

        $this->actingAs($this->membre())
            ->put(route('admin.metiers.update', $metier), [
                'secteur_id' => $metier->secteur_id,
                'nom' => $metier->nom,
                'slug' => $metier->slug,
                'accroche' => $metier->accroche,
                'est_publie' => '0',
            ])
            ->assertRedirect(route('admin.metiers.index'));

        $metier->refresh();

        $this->assertFalse($metier->est_publie);
        $this->assertNull($metier->publie_le);
        $this->get(route('metiers.show', $metier))->assertNotFound();
    }

    public function test_un_secteur_qui_contient_des_metiers_ne_peut_pas_etre_supprime(): void
    {
        $secteur = Secteur::factory()->create();
        Metier::factory()->for($secteur, 'secteur')->create();

        $this->actingAs($this->membre())
            ->delete(route('admin.secteurs.destroy', $secteur))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('secteurs', ['id' => $secteur->id]);
    }

    public function test_un_secteur_vide_peut_etre_supprime(): void
    {
        $secteur = Secteur::factory()->create();

        $this->actingAs($this->membre())
            ->delete(route('admin.secteurs.destroy', $secteur))
            ->assertRedirect(route('admin.secteurs.index'));

        $this->assertDatabaseMissing('secteurs', ['id' => $secteur->id]);
    }

    public function test_un_membre_enregistre_une_interview(): void
    {
        $metier = Metier::factory()->create();

        $this->actingAs($this->membre())
            ->post(route('admin.temoignages.store'), [
                'metier_id' => $metier->id,
                'nom' => 'Rachidi',
                'fonction' => 'Patron de garage',
                'ville' => 'Cotonou',
                'citation' => 'Je répare depuis vingt ans.',
                'plateforme' => 'instagram',
                'date_interview' => '2026-10-20',
                'est_publie' => '1',
            ])
            ->assertRedirect(route('admin.temoignages.index'));

        $this->assertDatabaseHas('temoignages', [
            'metier_id' => $metier->id,
            'nom' => 'Rachidi',
            'est_publie' => true,
        ]);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertSee('Je répare depuis vingt ans.');
    }

    public function test_une_plateforme_inconnue_est_refusee(): void
    {
        $metier = Metier::factory()->create();

        $this->actingAs($this->membre())
            ->post(route('admin.temoignages.store'), [
                'metier_id' => $metier->id,
                'nom' => 'Rachidi',
                'fonction' => 'Patron de garage',
                'ville' => 'Cotonou',
                'citation' => 'Je répare depuis vingt ans.',
                'plateforme' => 'facebook',
            ])
            ->assertSessionHasErrors('plateforme');
    }

    public function test_deux_metiers_ne_peuvent_pas_partager_le_meme_slug(): void
    {
        $secteur = Secteur::factory()->create();
        Metier::factory()->create(['slug' => 'soudeur']);

        $this->actingAs($this->membre())
            ->post(route('admin.metiers.store'), [
                'secteur_id' => $secteur->id,
                'nom' => 'Soudeur',
                'accroche' => 'Il assemble le métal.',
            ])
            ->assertSessionHasErrors('slug');
    }
}
