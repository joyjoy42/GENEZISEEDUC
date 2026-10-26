<?php

namespace Tests\Feature;

use App\Models\Metier;
use App\Models\Temoignage;
use App\Models\User;
use App\Supabase\StockageMedias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediasInterviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'supabase.url' => 'https://projet.supabase.co',
            'supabase.secret_key' => 'cle-de-test',
            'supabase.storage.bucket_interviews' => 'interviews',
        ]);

        // Le service est un singleton construit à partir de la configuration :
        // sans cet oubli, il garderait celle du démarrage.
        $this->app->forgetInstance(StockageMedias::class);
    }

    private function membre(): User
    {
        return User::factory()->create();
    }

    private function donnees(array $extra = []): array
    {
        return array_merge([
            'metier_id' => Metier::factory()->create()->id,
            'nom' => 'Jean',
            'fonction' => 'Couturier',
            'ville' => 'Cotonou',
            'citation' => "Tant que l'homme vit, il doit s'habiller.",
            'est_publie' => '1',
        ], $extra);
    }

    public function test_un_membre_envoie_un_rush_dans_le_coffre(): void
    {
        Http::fake(['projet.supabase.co/*' => Http::response(['Key' => 'interviews/x'], 200)]);

        $this->actingAs($this->membre())
            ->post(route('admin.temoignages.store'), $this->donnees([
                'media' => UploadedFile::fake()->create('Reel 22 oct.mp4', 2048, 'video/mp4'),
            ]))
            ->assertRedirect(route('admin.temoignages.index'));

        $temoignage = Temoignage::sole();

        $this->assertMatchesRegularExpression(
            '#^temoignages/\d{4}/reel-22-oct-[A-Za-z0-9]{8}\.mp4$#',
            $temoignage->fichier_video
        );

        Http::assertSent(fn (Request $requete) => $requete->method() === 'POST'
            && $requete->url() === 'https://projet.supabase.co/storage/v1/object/interviews/'.$temoignage->fichier_video
            && $requete->hasHeader('x-upsert', 'true'));
    }

    /**
     * Un envoi refusé ne doit pas perdre l'interview déjà saisie, et doit le
     * dire : sinon Joy croit son rush en ligne alors qu'il n'y est pas.
     */
    public function test_un_envoi_refuse_laisse_linterview_et_previent(): void
    {
        Http::fake(['projet.supabase.co/*' => Http::response('nope', 500)]);

        $this->actingAs($this->membre())
            ->post(route('admin.temoignages.store'), $this->donnees([
                'media' => UploadedFile::fake()->create('rush.mp4', 10, 'video/mp4'),
            ]))
            ->assertRedirect(route('admin.temoignages.index'))
            ->assertSessionHas('succes', fn (string $message) => str_contains($message, "l'envoi du média a échoué"));

        $this->assertNull(Temoignage::sole()->fichier_video);
    }

    public function test_remplacer_un_media_supprime_lancien(): void
    {
        Http::fake(['projet.supabase.co/*' => Http::response(['Key' => 'interviews/x'], 200)]);

        $temoignage = Temoignage::factory()->create(['fichier_video' => 'temoignages/2026/ancien-aaaaaaaa.mp4']);

        $this->actingAs($this->membre())
            ->put(route('admin.temoignages.update', $temoignage), $this->donnees([
                'metier_id' => $temoignage->metier_id,
                'media' => UploadedFile::fake()->create('nouveau.mp4', 10, 'video/mp4'),
            ]))
            ->assertRedirect(route('admin.temoignages.index'));

        $this->assertStringContainsString('nouveau-', $temoignage->refresh()->fichier_video);

        Http::assertSent(fn (Request $requete) => $requete->method() === 'DELETE'
            && str_ends_with($requete->url(), '/object/interviews/temoignages/2026/ancien-aaaaaaaa.mp4'));
    }

    public function test_le_lien_de_lecture_est_signe_et_temporaire(): void
    {
        Http::fake([
            'projet.supabase.co/storage/v1/object/sign/*' => Http::response([
                'signedURL' => '/object/sign/interviews/temoignages/2026/rush.mp4?token=jeton',
            ], 200),
        ]);

        $lien = app(StockageMedias::class)->lienSigne('temoignages/2026/rush.mp4');

        $this->assertSame(
            'https://projet.supabase.co/storage/v1/object/sign/interviews/temoignages/2026/rush.mp4?token=jeton',
            $lien
        );

        Http::assertSent(fn (Request $requete) => $requete['expiresIn'] === 3600);
    }

    /**
     * Le coffre est privé parce que ces fichiers ne sortent que sous
     * consentement écrit. La fiche publique ne doit donc pas livrer le chemin
     * du rush, qui est une adresse de téléchargement une fois le jeton obtenu.
     */
    public function test_la_fiche_publique_ne_montre_pas_le_chemin_du_rush(): void
    {
        $metier = Metier::factory()->create();

        Temoignage::factory()->for($metier, 'metier')->create([
            'video_url' => null,
            'fichier_video' => 'temoignages/2026/rush-secret.mp4',
        ]);

        $this->get(route('metiers.show', $metier))
            ->assertOk()
            ->assertDontSee('rush-secret')
            ->assertSee('Vidéo en cours de montage.');
    }

    /**
     * Le type est lu dans le contenu du fichier, pas dans son extension : un
     * exécutable renommé en .mp4 ne doit pas atteindre le coffre.
     */
    public function test_un_fichier_qui_nest_pas_un_media_est_refuse(): void
    {
        Http::fake();

        $this->actingAs($this->membre())
            ->post(route('admin.temoignages.store'), $this->donnees([
                'media' => UploadedFile::fake()->createWithContent('piege.mp4', 'MZ ce fichier est un exécutable'),
            ]))
            ->assertSessionHasErrors('media');

        Http::assertNothingSent();
    }
}
