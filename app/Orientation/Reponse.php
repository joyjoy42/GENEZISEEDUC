<?php

namespace App\Orientation;

/**
 * Les réponses possibles à une question du test.
 *
 * Trois choix nets valent mieux que cinq nuances : « Plutôt pas » et « Entre
 * les deux » ne veulent pas dire grand-chose quand on découvre les métiers.
 *
 * Les valeurs sont espacées (1, 3, 5) pour que « Pourquoi pas » tombe
 * exactement au milieu. C'est le seul endroit où l'échelle est définie :
 * le questionnaire, la validation et le calcul du profil la lisent tous ici.
 */
enum Reponse: int
{
    case Non = 1;
    case PourquoiPas = 3;
    case Oui = 5;

    public function libelle(): string
    {
        return match ($this) {
            self::Non => 'Non',
            self::PourquoiPas => 'Pourquoi pas',
            self::Oui => 'Oui',
        };
    }

    /**
     * Ce que la réponse veut dire, pour lever toute ambiguïté.
     */
    public function precision(): string
    {
        return match ($this) {
            self::Non => "Ce n'est pas moi",
            self::PourquoiPas => 'Je pourrais essayer',
            self::Oui => 'Ça me ressemble',
        };
    }

    /**
     * @return array<int, int>
     */
    public static function valeurs(): array
    {
        return array_map(fn (self $reponse) => $reponse->value, self::cases());
    }

    public static function minimum(): int
    {
        return min(self::valeurs());
    }

    /**
     * L'écart entre la réponse la plus basse et la plus haute : ce que vaut
     * une question au maximum dans le calcul du profil.
     */
    public static function amplitude(): int
    {
        return max(self::valeurs()) - self::minimum();
    }
}
