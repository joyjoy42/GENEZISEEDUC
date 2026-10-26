<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Secteur;
use App\Models\Temoignage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BibliothequeMetiersTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_bibliotheque_liste_les_metiers_publies(): void
    {
        $metier = Metier::factory()->create(['nom' => 'Couturier']);

        $this->get(route('metiers.index'))
            ->assertOk()
            ->assertSee('Couturier')
            ->assertSee($metier->accroche);
    }

    /**
     * Le premier jour, la bibliothèque est vide. Elle ne doit pas parler
     * d'un filtre ou d'un secteur que personne n'a choisi.
     */
    public function test_une_bibliotheque_vide_le_dit_sans_parler_de_filtre(): void
    {
        $this->get(route('metiers.index'))
            ->assertOk()
            ->assertSee('La bibliothèque est encore vide')
            ->assertDontSee('a pas encore de métier publié', false)
            ->assertDontSee('Voir tous les métiers');
    }

    public function test_un_secteur_filtre_et_vide_parle_bien_du_secteur(): void
    {
        $secteur = Secteur::factory()->create(['slug' => 'mode']);

        $this->get(route('metiers.index', ['secteur' => $secteur->slug]))
            ->assertOk()
            ->assertSee('Aucun métier ne correspond')
            ->assertSee('a pas encore de métier publié', false);
    }

    public function test_la_page_des_secteurs_vide_ne_reste_pas_muette(): void
    {
        $this->get(route('secteurs.index'))
            ->assertOk()
            ->assertSee('Aucun secteur pour le moment.');
    }

    public function test_laccueil_tient_debout_sans_aucun_secteur(): void
    {
        $this->get(route('accueil'))
            ->assertOk()
            ->assertDontSee('Par secteur');
    }

    public function test_un_metier_en_brouillon_reste_invisible_dans_la_liste(): void
    {
        Metier::factory()->brouillon()->create(['nom' => 'Métier secret']);

        $this->get(route('metiers.index'))
            ->assertOk()
            ->assertDontSee('Métier secret');
    }

    public function test_la_recherche_filtre_les_metiers_par_nom(): void
    {
        Metier::factory()->create(['nom' => 'Mécanicien automobile']);
        Metier::factory()->create(['nom' => 'Psychiatre']);

        $this->get(route('metiers.index', ['q' => 'mécanicien']))
            ->assertOk()
            ->assertSee('Mécanicien automobile')
            ->assertDontSee('Psychiatre');
    }

    /**
     * Un élève tape « mecanicien » sur son téléphone, sans accent. Il doit
     * trouver « Mécanicien automobile ».
     */
    public function test_la_recherche_ignore_les_accents_et_la_casse(): void
    {
        Metier::factory()->create(['nom' => 'Mécanicien automobile']);

        foreach (['mecanicien', 'Mécanicien', 'MECANICIEN', 'mécanicien'] as $terme) {
            $this->get(route('metiers.index', ['q' => $terme]))
                ->assertOk()
                ->assertSee('Mécanicien automobile');
        }
    }

    public function test_la_recherche_trouve_aussi_par_nom_de_secteur(): void
    {
        $secteur = Secteur::factory()->create(['nom' => 'Santé et santé mentale']);
        Metier::factory()->for($secteur, 'secteur')->create(['nom' => 'Psychiatre']);

        $this->get(route('metiers.index', ['q' => 'sante']))
            ->assertOk()
            ->assertSee('Psychiatre');
    }

    /**
     * `_` et `%` sont des jokers SQL. Sans échappement, chercher « _ »
     * remontait toute la bibliothèque.
     */
    public function test_les_jokers_sql_ne_remontent_pas_toute_la_bibliotheque(): void
    {
        Metier::factory()->create(['nom' => 'Couturier']);
        Metier::factory()->create(['nom' => 'Psychiatre']);

        foreach (['_', '%', '%%', '___'] as $terme) {
            $this->get(route('metiers.index', ['q' => $terme]))
                ->assertOk()
                ->assertSee('Aucun métier ne correspond');
        }
    }

    public function test_renommer_un_secteur_met_a_jour_la_recherche_de_ses_metiers(): void
    {
        $secteur = Secteur::factory()->create(['nom' => 'Ancien nom']);
        Metier::factory()->for($secteur, 'secteur')->create(['nom' => 'Couturier']);

        $secteur->update(['nom' => 'Mode et textile']);

        $this->get(route('metiers.index', ['q' => 'mode et textile']))
            ->assertOk()
            ->assertSee('Couturier');
    }

    public function test_le_filtre_par_secteur_ne_garde_que_ce_secteur(): void
    {
        $mode = Secteur::factory()->create(['nom' => 'Mode', 'slug' => 'mode']);
        $sante = Secteur::factory()->create(['nom' => 'Santé', 'slug' => 'sante']);

        Metier::factory()->for($mode, 'secteur')->create(['nom' => 'Couturier']);
        Metier::factory()->for($sante, 'secteur')->create(['nom' => 'Psychiatre']);

        $this->get(route('metiers.index', ['secteur' => 'mode']))
            ->assertOk()
            ->assertSee('Couturier')
            ->assertDontSee('Psychiatre');
    }

    public function test_la_fiche_metier_affiche_le_quotidien_et_les_idees_fausses(): void
    {
        $metier = Metier::factory()->create([
            'nom' => 'Mécanicien automobile',
            'idee_fausse' => "C'est un métier pour ceux qui ont échoué.",
            'realite' => 'Un bon mécanicien lit des schémas électriques.',
        ]);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertSee('Mécanicien automobile')
            ->assertSee("C'est un métier pour ceux qui ont échoué.")
            ->assertSee('Un bon mécanicien lit des schémas électriques.')
            ->assertSee('Le quotidien');
    }

    /**
     * Une filière sans formation dans le pays doit le dire sur la fiche, et
     * dire par quoi elle se finance : c'est ce qui bloque les élèves, pas
     * l'envie.
     */
    public function test_la_fiche_dit_le_financement_et_labsence_de_formation_au_pays(): void
    {
        $metier = Metier::factory()->create();

        $metier->filieres()->attach(Filiere::factory()->create([
            'nom' => 'Science informatique',
            'formation_disponible_pays' => false,
            'financements' => 'Bourses de coopération, vérifié en octobre 2026.',
        ])->id);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertSee('Aucun établissement connu au Bénin ne propose cette formation.')
            ->assertSee('Bourses de coopération, vérifié en octobre 2026.');
    }

    public function test_une_filiere_disponible_au_pays_naffiche_pas_lavertissement(): void
    {
        $metier = Metier::factory()->create();

        $metier->filieres()->attach(Filiere::factory()->create([
            'formation_disponible_pays' => true,
        ])->id);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertDontSee('Aucun établissement connu au Bénin');
    }

    public function test_la_fiche_dun_metier_en_brouillon_renvoie_404(): void
    {
        $metier = Metier::factory()->brouillon()->create();

        $this->get(route('metiers.show', $metier))->assertNotFound();
    }

    public function test_la_fiche_naffiche_que_les_interviews_publiees(): void
    {
        $metier = Metier::factory()->create();

        Temoignage::factory()->for($metier, 'metier')->create([
            'citation' => 'Je répare depuis vingt ans.',
        ]);

        Temoignage::factory()->for($metier, 'metier')->brouillon()->create([
            'citation' => 'Interview pas encore montée.',
        ]);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertSee('Je répare depuis vingt ans.')
            ->assertDontSee('Interview pas encore montée.');
    }

    public function test_sans_interview_la_fiche_le_dit_au_lieu_de_rester_vide(): void
    {
        $metier = Metier::factory()->create();

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertSee('Aucune interview publiée pour ce métier.', false);
    }

    public function test_la_fourchette_de_salaire_nest_affichee_que_si_elle_est_connue(): void
    {
        $sansSalaire = Metier::factory()->create();
        $avecSalaire = Metier::factory()->avecSalaire(60000, 150000)->create();

        $this->get(route('metiers.show', $sansSalaire))
            ->assertOk()
            ->assertSee('Pas encore de chiffre', false);

        $this->get(route('metiers.show', $avecSalaire))
            ->assertOk()
            ->assertSee('150 000 FCFA');
    }

    public function test_consulter_une_fiche_incremente_le_compteur_de_vues(): void
    {
        $metier = Metier::factory()->create(['vues' => 0]);

        $this->get(route('metiers.show', $metier))->assertOk();

        $this->assertSame(1, $metier->fresh()->vues);
    }

    public function test_la_page_dun_secteur_liste_ses_metiers_publies(): void
    {
        $secteur = Secteur::factory()->create(['nom' => 'Mode et textile', 'slug' => 'mode-textile']);

        Metier::factory()->for($secteur, 'secteur')->create(['nom' => 'Couturier']);
        Metier::factory()->for($secteur, 'secteur')->brouillon()->create(['nom' => 'Modéliste caché']);

        $this->get(route('secteurs.show', $secteur))
            ->assertOk()
            ->assertSee('Mode et textile')
            ->assertSee('Couturier')
            ->assertDontSee('Modéliste caché');
    }

    public function test_laccueil_annonce_les_compteurs_de_la_bibliotheque(): void
    {
        Metier::factory()->count(3)->create();
        Metier::factory()->brouillon()->create();

        $this->get(route('accueil'))
            ->assertOk()
            ->assertSee('Métiers documentés')
            ->assertSee('Tu ne peux pas choisir un métier que tu n&rsquo;as jamais vu.', false);
    }
}
