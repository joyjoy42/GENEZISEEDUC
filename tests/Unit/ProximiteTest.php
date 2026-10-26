<?php

namespace Tests\Unit;

use App\Orientation\Proximite;
use PHPUnit\Framework\TestCase;

class ProximiteTest extends TestCase
{
    public function test_une_forte_correlation_donne_tres_proche(): void
    {
        $this->assertSame(Proximite::TresProche, Proximite::depuisCorrelation(0.9));
        $this->assertSame(Proximite::TresProche, Proximite::depuisCorrelation(0.45));
    }

    public function test_une_correlation_moyenne_donne_proche(): void
    {
        $this->assertSame(Proximite::Proche, Proximite::depuisCorrelation(0.44));
        $this->assertSame(Proximite::Proche, Proximite::depuisCorrelation(0.15));
    }

    public function test_une_correlation_faible_donne_un_peu_a_cote(): void
    {
        $this->assertSame(Proximite::UnPeuACote, Proximite::depuisCorrelation(0.14));
        $this->assertSame(Proximite::UnPeuACote, Proximite::depuisCorrelation(0.0));
        $this->assertSame(Proximite::UnPeuACote, Proximite::depuisCorrelation(-0.15));
    }

    public function test_une_correlation_negative_donne_loin(): void
    {
        $this->assertSame(Proximite::Loin, Proximite::depuisCorrelation(-0.16));
        $this->assertSame(Proximite::Loin, Proximite::depuisCorrelation(-1.0));
    }

    public function test_seuls_les_metiers_proches_sont_mis_en_avant(): void
    {
        $this->assertTrue(Proximite::TresProche->vautLaPeine());
        $this->assertTrue(Proximite::Proche->vautLaPeine());
        $this->assertFalse(Proximite::UnPeuACote->vautLaPeine());
        $this->assertFalse(Proximite::Loin->vautLaPeine());
    }
}
