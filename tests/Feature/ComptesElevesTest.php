<?php

namespace Tests\Feature;

use App\Models\Eleve;
use App\Models\Question;
use App\Models\ResultatTest;
use App\Supabase\ComptesEleves;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ComptesElevesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'supabase.url' => 'https://projet.supabase.co',
            'supabase.secret_key' => 'cle-de-test',
        ]);

        $this->app->forgetInstance(ComptesEleves::class);
    }

    private function inscriptionAcceptee(string $id = '11111111-1111-1111-1111-111111111111'): void
    {
        Http::fake([
            'projet.supabase.co/auth/v1/admin/users' => Http::response(['id' => $id], 200),
            'projet.supabase.co/auth/v1/token*' => Http::response(['user' => ['id' => $id]], 200),
        ]);
    }

    private function formulaire(array $extra = []): array
    {
        return array_merge([
            'email' => 'awa@exemple.bj',
            'password' => 'motdepasse-solide',
            'password_confirmation' => 'motdepasse-solide',
            'annee_naissance' => (int) date('Y') - 17,
        ], $extra);
    }

    public function test_une_eleve_cree_son_compte(): void
    {
        $this->inscriptionAcceptee();

        $this->post(route('eleves.inscrire'), $this->formulaire())
            ->assertRedirect(route('eleves.suivi'));

        $eleve = Eleve::sole();

        $this->assertSame('awa@exemple.bj', $eleve->email);
        $this->assertSame('11111111-1111-1111-1111-111111111111', $eleve->supabase_id);
        $this->assertAuthenticatedAs($eleve, 'eleve');
    }

    /**
     * Le mot de passe est chez Supabase, et nulle part ici : une copie locale
     * serait une base de mots de passe de mineurs à protéger pour rien.
     */
    public function test_le_mot_de_passe_nest_pas_conserve_en_base(): void
    {
        $this->inscriptionAcceptee();

        $this->post(route('eleves.inscrire'), $this->formulaire());

        $colonnes = array_keys(Eleve::sole()->getAttributes());

        $this->assertNotContains('password', $colonnes);
        $this->assertNotContains('mot_de_passe', $colonnes);
    }

    public function test_un_eleve_de_moins_de_quinze_ans_na_pas_de_compte(): void
    {
        Http::fake();

        $this->post(route('eleves.inscrire'), $this->formulaire([
            'annee_naissance' => (int) date('Y') - 14,
        ]))->assertSessionHasErrors('annee_naissance');

        $this->assertSame(0, Eleve::count());
        Http::assertNothingSent();
    }

    public function test_une_adresse_deja_prise_est_refusee(): void
    {
        Http::fake();
        Eleve::factory()->create(['email' => 'awa@exemple.bj']);

        $this->post(route('eleves.inscrire'), $this->formulaire())
            ->assertSessionHasErrors('email');

        Http::assertNothingSent();
    }

    public function test_un_mauvais_mot_de_passe_ne_connecte_personne(): void
    {
        Http::fake(['projet.supabase.co/auth/v1/token*' => Http::response(['error' => 'invalid'], 400)]);
        Eleve::factory()->create(['email' => 'awa@exemple.bj']);

        $this->post(route('eleves.connecter'), [
            'email' => 'awa@exemple.bj',
            'password' => 'faux',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('eleve');
    }

    /**
     * Le résultat passé juste avant l'inscription suit : sinon l'élève le
     * perdrait au moment même où il crée un compte pour le garder.
     */
    public function test_le_resultat_passe_avant_linscription_est_rattache(): void
    {
        $this->inscriptionAcceptee();
        $resultat = ResultatTest::factory()->create();

        $this->withSession(['dernier_resultat' => $resultat->jeton])
            ->post(route('eleves.inscrire'), $this->formulaire());

        $this->assertSame(Eleve::sole()->id, $resultat->refresh()->eleve_id);
    }

    public function test_le_suivi_ne_montre_que_ses_propres_resultats(): void
    {
        $awa = Eleve::factory()->create();
        $sien = ResultatTest::factory()->create(['eleve_id' => $awa->id]);
        $autre = ResultatTest::factory()->create(['eleve_id' => Eleve::factory()->create()->id]);

        $this->actingAs($awa, 'eleve')
            ->get(route('eleves.suivi'))
            ->assertOk()
            ->assertSee(route('test.resultat', $sien))
            ->assertDontSee(route('test.resultat', $autre));
    }

    /**
     * Le point qui justifie une table et un garde séparés : une session élève
     * ne doit jamais ouvrir le back-office.
     */
    public function test_une_session_eleve_nouvre_pas_le_back_office(): void
    {
        $this->actingAs(Eleve::factory()->create(), 'eleve')
            ->get(route('admin.tableau-bord'))
            ->assertRedirect(route('login'));
    }

    public function test_une_eleve_supprime_son_compte(): void
    {
        Http::fake(['projet.supabase.co/auth/v1/admin/users/*' => Http::response('', 200)]);

        $eleve = Eleve::factory()->create();
        $resultat = ResultatTest::factory()->create(['eleve_id' => $eleve->id]);

        $this->actingAs($eleve, 'eleve')
            ->delete(route('eleves.supprimer'))
            ->assertRedirect(route('accueil'));

        $this->assertSame(0, Eleve::count());
        $this->assertGuest('eleve');

        // Le résultat survit, mais redevient anonyme : c'est ce qu'il était
        // avant la création du compte.
        $this->assertNull($resultat->refresh()->eleve_id);

        Http::assertSent(fn ($requete) => $requete->method() === 'DELETE'
            && str_ends_with($requete->url(), '/auth/v1/admin/users/'.$eleve->supabase_id));
    }

    /**
     * Si Supabase refuse, on ne touche à rien ici : sinon l'adresse resterait
     * prise chez Supabase par un compte que plus personne ne peut atteindre.
     */
    public function test_un_echec_cote_supabase_laisse_le_compte_intact(): void
    {
        Http::fake(['projet.supabase.co/auth/v1/admin/users/*' => Http::response('nope', 500)]);

        $eleve = Eleve::factory()->create();

        $this->actingAs($eleve, 'eleve')
            ->from(route('eleves.suppression'))
            ->delete(route('eleves.supprimer'))
            ->assertRedirect(route('eleves.suppression'))
            ->assertSessionHasErrors('suppression');

        $this->assertSame(1, Eleve::count());
        $this->assertAuthenticatedAs($eleve->fresh(), 'eleve');
    }

    public function test_la_suppression_est_fermee_aux_visiteurs(): void
    {
        $this->delete(route('eleves.supprimer'))->assertRedirect(route('login'));
    }

    public function test_le_suivi_est_ferme_aux_visiteurs(): void
    {
        $this->get(route('eleves.suivi'))->assertRedirect(route('login'));
    }

    public function test_le_test_passe_connecte_est_rattache_au_compte(): void
    {
        $eleve = Eleve::factory()->create();
        $questions = Question::factory()->count(3)->create(['est_active' => true]);

        $this->actingAs($eleve, 'eleve')
            ->post(route('test.calculer'), [
                'reponses' => $questions->mapWithKeys(fn ($question) => [$question->id => 5])->all(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($eleve->id, ResultatTest::sole()->eleve_id);
    }
}
