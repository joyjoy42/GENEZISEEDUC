<?php

namespace App\Orientation;

use App\Models\Metier;
use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Un profil RIASEC : une note de 0 à 100 par dimension.
 *
 * Le même objet sert pour un étudiant (calculé depuis ses réponses) et pour
 * un métier (lu depuis sa fiche), ce qui permet de les comparer directement.
 */
final class Profil
{
    /**
     * @param  array<string, int>  $scores  indexé par la valeur de Dimension
     */
    private function __construct(private readonly array $scores) {}

    /**
     * @param  array<string, int>  $scores
     */
    public static function depuisTableau(array $scores): self
    {
        $complet = [];

        foreach (Dimension::cases() as $dimension) {
            $complet[$dimension->value] = self::borne((int) ($scores[$dimension->value] ?? 0));
        }

        return new self($complet);
    }

    public static function depuisMetier(Metier $metier): self
    {
        $scores = [];

        foreach (Dimension::cases() as $dimension) {
            $scores[$dimension->value] = (int) $metier->{$dimension->colonne()};
        }

        return new self($scores);
    }

    /**
     * Calcule le profil d'un étudiant à partir de ses réponses.
     *
     * On retranche la réponse la plus basse pour qu'un « Non » vaille zéro,
     * puis on ramène chaque dimension sur 100 — sinon une dimension avec plus
     * de questions pèserait plus lourd.
     *
     * @param  array<int, int>  $reponses  id de question => valeur d'une Reponse
     * @param  Collection<int, Question>  $questions
     */
    public static function depuisReponses(array $reponses, Collection $questions): self
    {
        $scores = [];
        $plancher = Reponse::minimum();

        foreach (Dimension::cases() as $dimension) {
            $deLaDimension = $questions->where('dimension', $dimension->value);
            $maximum = $deLaDimension->count() * Reponse::amplitude();

            if ($maximum === 0) {
                $scores[$dimension->value] = 0;

                continue;
            }

            $obtenu = $deLaDimension->sum(
                fn (Question $question) => max(0, (int) ($reponses[$question->id] ?? $plancher) - $plancher)
            );

            $scores[$dimension->value] = (int) round($obtenu / $maximum * 100);
        }

        return new self($scores);
    }

    public function score(Dimension $dimension): int
    {
        return $this->scores[$dimension->value];
    }

    /**
     * Les dimensions les plus fortes, de la plus marquée à la moins marquée.
     *
     * @return array<int, Dimension>
     */
    public function dominantes(int $combien = 3): array
    {
        return collect(Dimension::cases())
            ->sortByDesc(fn (Dimension $dimension) => $this->score($dimension))
            ->take($combien)
            ->values()
            ->all();
    }

    /**
     * Le code Holland, par exemple « RIE ».
     */
    public function code(): string
    {
        return collect($this->dominantes())
            ->map(fn (Dimension $dimension) => mb_substr($dimension->libelle(), 0, 1))
            ->implode('');
    }

    public function estVide(): bool
    {
        return $this->total() === 0;
    }

    public function total(): int
    {
        return array_sum($this->scores);
    }

    /**
     * Corrélation entre deux profils, de -1 (opposés) à +1 (identiques).
     *
     * On retranche la moyenne de chaque profil avant de comparer. Sans ce
     * centrage, tous les scores étant positifs, deux profils quelconques se
     * ressemblent déjà beaucoup et tout se tasse entre 70 et 90 % : on ne
     * distinguait plus le métier vraiment proche du métier simplement pas loin.
     *
     * Une fois centré, c'est l'écart à sa propre moyenne qui compte, donc ce
     * qui ressort chez quelqu'un comparé à ce qui ressort dans le métier.
     */
    public function correlationAvec(self $autre): float
    {
        if ($this->estPlat() || $autre->estPlat()) {
            return 0.0;
        }

        $moyenneA = $this->moyenne();
        $moyenneB = $autre->moyenne();

        $produit = 0.0;
        $varianceA = 0.0;
        $varianceB = 0.0;

        foreach (Dimension::cases() as $dimension) {
            $a = $this->score($dimension) - $moyenneA;
            $b = $autre->score($dimension) - $moyenneB;

            $produit += $a * $b;
            $varianceA += $a ** 2;
            $varianceB += $b ** 2;
        }

        return $produit / (sqrt($varianceA) * sqrt($varianceB));
    }

    /**
     * La corrélation ramenée sur un indice de 0 à 100, lisible par un étudiant.
     *
     * 50 veut dire « aucun rapport », au-dessus « ça va dans ton sens »,
     * en dessous « c'est plutôt l'inverse de ce qui te ressemble ».
     */
    public function affiniteAvec(self $autre): int
    {
        return (int) round(($this->correlationAvec($autre) + 1) / 2 * 100);
    }

    public function moyenne(): float
    {
        return $this->total() / count(Dimension::cases());
    }

    /**
     * Un profil plat — six notes identiques, zéros compris — ne dit rien :
     * il ne peut être ni comparé ni classé.
     */
    public function estPlat(): bool
    {
        return count(array_unique($this->scores)) === 1;
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return $this->scores;
    }

    /**
     * Les scores prêts à être écrits en base, indexés par nom de colonne.
     *
     * @return array<string, int>
     */
    public function enColonnes(): array
    {
        $colonnes = [];

        foreach (Dimension::cases() as $dimension) {
            $colonnes[$dimension->colonne()] = $this->score($dimension);
        }

        return $colonnes;
    }

    private static function borne(int $valeur): int
    {
        return max(0, min(100, $valeur));
    }
}
