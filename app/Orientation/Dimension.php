<?php

namespace App\Orientation;

/**
 * Les six familles d'intérêts du modèle RIASEC, utilisé par les conseillers
 * d'orientation depuis les travaux de John Holland.
 *
 * Elles décrivent ce vers quoi quelqu'un est attiré, pas ce dont il est capable :
 * un score bas ne ferme aucune porte.
 */
enum Dimension: string
{
    case Realiste = 'realiste';
    case Investigateur = 'investigateur';
    case Artistique = 'artistique';
    case Social = 'social';
    case Entreprenant = 'entreprenant';
    case Conventionnel = 'conventionnel';

    /**
     * La colonne qui porte cette note, dans `metiers` comme dans `resultat_tests`.
     */
    public function colonne(): string
    {
        return 'score_'.$this->value;
    }

    public function libelle(): string
    {
        return match ($this) {
            self::Realiste => 'Réaliste',
            self::Investigateur => 'Investigateur',
            self::Artistique => 'Artistique',
            self::Social => 'Social',
            self::Entreprenant => 'Entreprenant',
            self::Conventionnel => 'Conventionnel',
        };
    }

    /**
     * Ce que la dimension dit de quelqu'un, en une phrase compréhensible.
     */
    public function description(): string
    {
        return match ($this) {
            self::Realiste => 'Tu aimes le concret : les mains, les outils, les machines, le terrain.',
            self::Investigateur => 'Tu aimes comprendre : chercher, analyser, aller au fond des choses.',
            self::Artistique => 'Tu aimes créer : imaginer, donner une forme, sortir du cadre.',
            self::Social => 'Tu aimes les gens : aider, expliquer, accompagner, transmettre.',
            self::Entreprenant => 'Tu aimes mener : convaincre, décider, lancer, vendre.',
            self::Conventionnel => 'Tu aimes l\'ordre : organiser, vérifier, tenir les choses au carré.',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::Realiste => '#B5481A',
            self::Investigateur => '#1B1F3B',
            self::Artistique => '#E8743B',
            self::Social => '#2E7D6E',
            self::Entreprenant => '#8A5A2B',
            self::Conventionnel => '#3E4466',
        };
    }
}
