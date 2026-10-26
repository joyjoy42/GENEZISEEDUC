<?php

namespace Tests\Unit;

use App\Orientation\Dimension;
use App\Orientation\Profil;
use PHPUnit\Framework\TestCase;

class ProfilTest extends TestCase
{
    public function test_les_scores_absents_valent_zero(): void
    {
        $profil = Profil::depuisTableau(['social' => 60]);

        $this->assertSame(60, $profil->score(Dimension::Social));
        $this->assertSame(0, $profil->score(Dimension::Realiste));
    }

    public function test_les_scores_sont_bornes_entre_0_et_100(): void
    {
        $profil = Profil::depuisTableau(['social' => 180, 'realiste' => -40]);

        $this->assertSame(100, $profil->score(Dimension::Social));
        $this->assertSame(0, $profil->score(Dimension::Realiste));
    }

    public function test_les_dominantes_sortent_de_la_plus_forte_a_la_plus_faible(): void
    {
        $profil = Profil::depuisTableau([
            'realiste' => 20, 'investigateur' => 80, 'artistique' => 10,
            'social' => 95, 'entreprenant' => 50, 'conventionnel' => 5,
        ]);

        $this->assertSame(
            [Dimension::Social, Dimension::Investigateur, Dimension::Entreprenant],
            $profil->dominantes()
        );

        $this->assertSame('SIE', $profil->code());
    }

    public function test_deux_profils_identiques_sont_parfaitement_correles(): void
    {
        $profil = Profil::depuisTableau(['social' => 90, 'investigateur' => 40]);

        $this->assertEqualsWithDelta(1.0, $profil->correlationAvec($profil), 0.0001);
        $this->assertSame(100, $profil->affiniteAvec($profil));
    }

    public function test_deux_profils_opposes_sont_correles_negativement(): void
    {
        $social = Profil::depuisTableau(['social' => 100]);
        $realiste = Profil::depuisTableau(['realiste' => 100]);

        $this->assertLessThan(0, $social->correlationAvec($realiste));
        $this->assertLessThan(50, $social->affiniteAvec($realiste));
    }

    /**
     * C'est la propriété qui compte : quelqu'un qui répond prudemment partout
     * doit obtenir les mêmes métiers que quelqu'un qui répond franchement.
     */
    public function test_la_correlation_ignore_lintensite_et_ne_regarde_que_la_forme(): void
    {
        $franc = Profil::depuisTableau(['social' => 100, 'investigateur' => 50]);
        $prudent = Profil::depuisTableau(['social' => 40, 'investigateur' => 20]);
        $metier = Profil::depuisTableau(['social' => 90, 'investigateur' => 45]);

        $this->assertEqualsWithDelta(
            $franc->correlationAvec($metier),
            $prudent->correlationAvec($metier),
            0.0001
        );
    }

    /**
     * Le défaut qu'on vient de corriger : avec une simple similarité cosinus,
     * tous les métiers se tenaient entre 68 et 88. L'écart doit être franc.
     */
    public function test_un_metier_proche_et_un_metier_eloigne_sont_nettement_separes(): void
    {
        $etudiant = Profil::depuisTableau([
            'social' => 100, 'artistique' => 75, 'entreprenant' => 50,
            'realiste' => 25, 'investigateur' => 25, 'conventionnel' => 25,
        ]);

        $psychologue = Profil::depuisTableau([
            'realiste' => 10, 'investigateur' => 70, 'artistique' => 35,
            'social' => 95, 'entreprenant' => 30, 'conventionnel' => 40,
        ]);

        $ingenieur = Profil::depuisTableau([
            'realiste' => 65, 'investigateur' => 85, 'artistique' => 30,
            'social' => 25, 'entreprenant' => 40, 'conventionnel' => 60,
        ]);

        $ecart = $etudiant->affiniteAvec($psychologue) - $etudiant->affiniteAvec($ingenieur);

        $this->assertGreaterThan(30, $ecart);
    }

    public function test_un_profil_plat_ne_ressemble_a_rien(): void
    {
        $plat = Profil::depuisTableau(['social' => 50, 'realiste' => 50, 'artistique' => 50,
            'investigateur' => 50, 'entreprenant' => 50, 'conventionnel' => 50]);
        $plein = Profil::depuisTableau(['social' => 80]);

        $this->assertTrue($plat->estPlat());
        $this->assertSame(0.0, $plat->correlationAvec($plein));
        $this->assertSame(0.0, $plein->correlationAvec($plat));
    }

    public function test_un_profil_entierement_a_zero_est_plat_et_vide(): void
    {
        $vide = Profil::depuisTableau([]);

        $this->assertTrue($vide->estVide());
        $this->assertTrue($vide->estPlat());
    }

    public function test_un_profil_avec_un_seul_ecart_nest_pas_plat(): void
    {
        $this->assertFalse(Profil::depuisTableau(['social' => 1])->estPlat());
    }

    public function test_les_scores_se_traduisent_en_colonnes_de_base(): void
    {
        $colonnes = Profil::depuisTableau(['social' => 80])->enColonnes();

        $this->assertArrayHasKey('score_social', $colonnes);
        $this->assertSame(80, $colonnes['score_social']);
        $this->assertSame(0, $colonnes['score_realiste']);
    }
}
