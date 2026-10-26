<?php

namespace App\Orientation;

/**
 * Ce que vaut une corrélation, dit en mots.
 *
 * Un nombre seul se lit mal : « 56 » ne veut rien dire pour quelqu'un qui
 * découvre les métiers. Le libellé porte le sens, le nombre vient après.
 */
enum Proximite: string
{
    case TresProche = 'tres-proche';
    case Proche = 'proche';
    case UnPeuACote = 'un-peu-a-cote';
    case Loin = 'loin';

    public static function depuisCorrelation(float $correlation): self
    {
        return match (true) {
            $correlation >= 0.45 => self::TresProche,
            $correlation >= 0.15 => self::Proche,
            $correlation >= -0.15 => self::UnPeuACote,
            default => self::Loin,
        };
    }

    public function libelle(): string
    {
        return match ($this) {
            self::TresProche => 'Très proche de ton profil',
            self::Proche => 'Proche de ton profil',
            self::UnPeuACote => 'Un peu à côté',
            self::Loin => 'Loin de ton profil',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::TresProche => '#2E7D6E',
            self::Proche => '#8A5A2B',
            self::UnPeuACote, self::Loin => '#6B7090',
        };
    }

    /**
     * Les métiers qu'on met en avant : ceux qui vont au moins un peu dans le
     * sens du profil. En dessous, on ne propose rien plutôt que de remplir.
     */
    public function vautLaPeine(): bool
    {
        return $this === self::TresProche || $this === self::Proche;
    }
}
