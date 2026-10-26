<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Question;
use App\Models\ResultatTest;
use App\Orientation\Dimension;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestOrientationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Deux questions par dimension, pour avoir un questionnaire équilibré.
     *
     * @return Collection<int, Question>
     */
    private function questionnaire(): Collection
    {
        $questions = new Collection;

        foreach (Dimension::cases() as $dimension) {
            $questions = $questions->merge(
                Question::factory()->count(2)->pour($dimension)->create()
            );
        }

        return $questions;
    }

    /**
     * Répond `valeur` aux questions de `dimension`, et 1 partout ailleurs.
     *
     * @return array<int, int>
     */
    private function reponses(Collection $questions, Dimension $dimension, int $valeur = 5): array
    {
        return $questions
            ->mapWithKeys(fn (Question $question) => [
                $question->id => $question->dimension === $dimension ? $valeur : 1,
            ])
            ->all();
    }

    public function test_la_page_dintroduction_annonce_le_nombre_de_questions(): void
    {
        $this->questionnaire();

        $this->get(route('test.intro'))
            ->assertOk()
            ->assertSee('12 questions pour savoir par où commencer');
    }

    public function test_le_questionnaire_naffiche_que_les_questions_actives(): void
    {
        Question::factory()->create(['libelle' => 'Une question posée']);
        Question::factory()->retiree()->create(['libelle' => 'Une question retirée']);

        $this->get(route('test.questions'))
            ->assertOk()
            ->assertSee('Une question posée')
            ->assertDontSee('Une question retirée');
    }

    public function test_un_questionnaire_incomplet_est_refuse(): void
    {
        $questions = $this->questionnaire();
        $partielles = collect($this->reponses($questions, Dimension::Realiste))->take(3)->all();

        $this->post(route('test.calculer'), ['reponses' => $partielles])
            ->assertSessionHasErrors('reponses');

        $this->assertDatabaseCount('resultat_tests', 0);
    }

    /**
     * Le questionnaire ne propose que Non, Pourquoi pas et Oui : toute autre
     * valeur vient d'ailleurs que du formulaire.
     */
    public function test_une_reponse_hors_bareme_est_refusee(): void
    {
        $questions = $this->questionnaire();

        foreach ([0, 2, 4, 9, -1] as $horsBareme) {
            $reponses = $this->reponses($questions, Dimension::Realiste);
            $reponses[$questions->first()->id] = $horsBareme;

            $this->post(route('test.calculer'), ['reponses' => $reponses])
                ->assertSessionHasErrors();
        }

        $this->assertDatabaseCount('resultat_tests', 0);
    }

    public function test_le_questionnaire_propose_trois_reponses_nettes(): void
    {
        Question::factory()->create(['libelle' => 'Travailler avec mes mains']);

        $reponse = $this->get(route('test.questions'))->assertOk();

        foreach (['Non', 'Pourquoi pas', 'Oui'] as $libelle) {
            $reponse->assertSee($libelle);
        }

        foreach (['Plutôt pas', 'Entre les deux', 'Plutôt oui', 'Tout à fait'] as $ancien) {
            $reponse->assertDontSee($ancien);
        }
    }

    public function test_un_questionnaire_complet_produit_un_profil_et_une_page_de_resultat(): void
    {
        $questions = $this->questionnaire();

        $reponse = $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $resultat = ResultatTest::sole();

        $reponse->assertRedirect(route('test.resultat', $resultat));

        $this->assertSame(100, $resultat->score_social);
        $this->assertSame(0, $resultat->score_realiste);
        $this->assertSame(12, $resultat->nombre_reponses);

        $this->get(route('test.resultat', $resultat))
            ->assertOk()
            ->assertSee('Social');
    }

    public function test_un_profil_modere_garde_la_meme_forme_quun_profil_tranche(): void
    {
        $questions = $this->questionnaire();

        $this->post(route('test.calculer'), ['reponses' => $this->reponses($questions, Dimension::Artistique, 5)]);
        $tranche = ResultatTest::latest('id')->firstOrFail();

        $this->post(route('test.calculer'), ['reponses' => $this->reponses($questions, Dimension::Artistique, 3)]);
        $modere = ResultatTest::latest('id')->firstOrFail();

        $this->assertSame(
            $tranche->profil()->dominantes(1),
            $modere->profil()->dominantes(1)
        );
    }

    public function test_le_resultat_classe_les_metiers_du_plus_proche_au_moins_proche(): void
    {
        $questions = $this->questionnaire();

        Metier::factory()
            ->profil(['social' => 95, 'investigateur' => 40])
            ->create(['nom' => 'Psychologue']);

        Metier::factory()
            ->profil(['social' => 70, 'artistique' => 50])
            ->create(['nom' => 'Enseignant']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $contenu = $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($contenu, 'Enseignant'),
            strpos($contenu, 'Psychologue'),
            'Le métier le plus proche du profil doit apparaître en premier.'
        );
    }

    public function test_un_metier_a_loppose_du_profil_nest_pas_propose(): void
    {
        $questions = $this->questionnaire();

        Metier::factory()->profil(['social' => 95])->create(['nom' => 'Psychologue']);
        Metier::factory()->profil(['realiste' => 95, 'conventionnel' => 30])->create(['nom' => 'Mécanicien']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('Psychologue')
            ->assertDontSee('Mécanicien');
    }

    /**
     * Avec peu de métiers en ligne, une page à un seul résultat paraît vide :
     * on la complète avec les métiers neutres, jamais avec les opposés.
     */
    public function test_les_metiers_neutres_completent_la_liste_mais_pas_les_opposes(): void
    {
        $questions = $this->questionnaire();

        // Va dans le sens du profil.
        Metier::factory()
            ->profil(['social' => 95, 'investigateur' => 40])
            ->create(['nom' => 'Psychologue']);

        // Ni dans un sens ni dans l'autre : un profil sans relief.
        Metier::factory()->profil([
            'realiste' => 50, 'investigateur' => 50, 'artistique' => 50,
            'social' => 50, 'entreprenant' => 50, 'conventionnel' => 50,
        ])->create(['nom' => 'Métier polyvalent']);

        // À l'opposé du profil.
        Metier::factory()
            ->profil(['realiste' => 95, 'conventionnel' => 30])
            ->create(['nom' => 'Mécanicien']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('Psychologue')
            ->assertSee('Métier polyvalent')
            ->assertSee('Un peu à côté')
            ->assertDontSee('Mécanicien');
    }

    /**
     * Si rien ne ressort, on montre quand même les plus proches en le disant,
     * plutôt que de renvoyer l'étudiant les mains vides.
     */
    public function test_quand_aucun_metier_ne_ressort_les_plus_proches_sont_montres(): void
    {
        $questions = $this->questionnaire();

        Metier::factory()->profil(['realiste' => 95, 'conventionnel' => 30])->create(['nom' => 'Mécanicien']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('ne ressort nettement pour toi', false)
            ->assertSee('Mécanicien');
    }

    /**
     * Quelqu'un qui répond la même chose à tout n'a pas de profil : le dire
     * vaut mieux que de classer des métiers au hasard.
     */
    public function test_un_profil_plat_ne_declenche_aucune_recommandation(): void
    {
        $questions = $this->questionnaire();
        Metier::factory()->profil(['social' => 95])->create(['nom' => 'Psychologue']);

        $reponses = $questions->mapWithKeys(fn (Question $question) => [$question->id => 3])->all();

        $this->post(route('test.calculer'), ['reponses' => $reponses]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('Tes réponses se ressemblent toutes.')
            ->assertDontSee('Psychologue');
    }

    public function test_un_metier_sans_profil_nest_jamais_recommande(): void
    {
        $questions = $this->questionnaire();

        Metier::factory()->create(['nom' => 'Métier sans profil']);
        Metier::factory()->profil(['social' => 90])->create(['nom' => 'Psychologue']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('Psychologue')
            ->assertDontSee('Métier sans profil');
    }

    public function test_un_metier_en_brouillon_nest_jamais_recommande(): void
    {
        $questions = $this->questionnaire();

        Metier::factory()->brouillon()->profil(['social' => 95])->create(['nom' => 'Fiche cachée']);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertDontSee('Fiche cachée');
    }

    public function test_le_resultat_propose_les_filieres_des_metiers_recommandes(): void
    {
        $questions = $this->questionnaire();

        $metier = Metier::factory()->profil(['social' => 95])->create(['nom' => 'Psychologue']);
        $filiere = Filiere::factory()->create(['nom' => 'Licence de psychologie']);
        $metier->filieres()->attach($filiere);

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Social),
        ]);

        $this->get(route('test.resultat', ResultatTest::sole()))
            ->assertOk()
            ->assertSee('Licence de psychologie');
    }

    /**
     * Une classe entière dans un lycée ou un cybercafé partage une seule
     * adresse IP. L'ancienne limite, de 6 par minute et par IP, bloquait le
     * 7e élève alors qu'il passait le test pour la première fois.
     *
     * Le client de test ouvre une session neuve à chaque requête : c'est
     * précisément le cas d'élèves différents derrière la même adresse. La
     * limite par session, elle, ne peut pas être exercée ici — elle ne tient
     * que grâce au cookie d'un vrai navigateur.
     */
    public function test_une_classe_entiere_derriere_la_meme_ip_peut_passer_le_test(): void
    {
        $questions = $this->questionnaire();
        $reponses = $this->reponses($questions, Dimension::Social);

        for ($eleve = 0; $eleve < 30; $eleve++) {
            $this->post(route('test.calculer'), ['reponses' => $reponses])->assertRedirect();
        }

        $this->assertSame(30, ResultatTest::count());

        // Au-delà, le plafond par IP arrête un envoi automatisé.
        $this->post(route('test.calculer'), ['reponses' => $reponses])->assertStatus(429);

        $this->assertSame(30, ResultatTest::count());
    }

    public function test_un_jeton_inconnu_renvoie_404(): void
    {
        $this->get(route('test.resultat', 'jeton-qui-nexiste-pas'))->assertNotFound();
    }

    public function test_le_resultat_ne_conserve_aucune_donnee_personnelle(): void
    {
        $questions = $this->questionnaire();

        $this->post(route('test.calculer'), [
            'reponses' => $this->reponses($questions, Dimension::Entreprenant),
        ]);

        $colonnes = array_keys(ResultatTest::sole()->getAttributes());

        $this->assertSame([
            'id', 'jeton',
            'score_realiste', 'score_investigateur', 'score_artistique',
            'score_social', 'score_entreprenant', 'score_conventionnel',
            'nombre_reponses', 'created_at', 'updated_at',
        ], $colonnes);
    }
}
