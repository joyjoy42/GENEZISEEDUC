<?php

namespace App\Orientation;

use App\Models\Filiere;
use App\Models\Metier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Rapproche le profil d'un étudiant des métiers de la bibliothèque.
 *
 * On ne garde que les métiers publiés dont le profil a été renseigné : un
 * métier laissé à zéro ne ressemble à personne et polluerait le classement.
 */
final class Recommandeur
{
    public function __construct(private readonly Profil $profil) {}

    /**
     * Les métiers qui vont dans le sens du profil, du plus proche au moins proche.
     *
     * On s'arrête à ceux qui vont vraiment dans ce sens : afficher toute la
     * bibliothèque avec un pourcentage à côté donne l'illusion que tout
     * correspond un peu.
     *
     * @return Collection<int, array{metier: Metier, affinite: int, proximite: Proximite}>
     */
    public function metiers(int $limite = 6, int $minimum = 3): Collection
    {
        if ($this->profil->estPlat()) {
            return collect();
        }

        $candidats = $this->tousLesMetiers();

        $retenus = $candidats
            ->filter(fn (array $entree) => $entree['proximite']->vautLaPeine())
            ->take($limite);

        // Une page avec un seul métier paraît vide. On la complète avec les
        // métiers neutres — jamais avec ceux qui sont à l'opposé du profil.
        if ($retenus->count() < $minimum) {
            $neutres = $candidats
                ->reject(fn (array $entree) => $entree['proximite']->vautLaPeine())
                ->filter(fn (array $entree) => $entree['proximite'] !== Proximite::Loin)
                ->take($minimum - $retenus->count());

            $retenus = $retenus->concat($neutres);
        }

        return $retenus->values();
    }

    /**
     * Le métier le moins éloigné, quel qu'il soit.
     *
     * Sert quand rien ne ressort du tout : mieux vaut un point de départ
     * annoncé comme tel que de renvoyer un étudiant les mains vides.
     *
     * @return array{metier: Metier, affinite: int, proximite: Proximite}|null
     */
    public function lePlusProche(): ?array
    {
        if ($this->profil->estPlat()) {
            return null;
        }

        return $this->tousLesMetiers()->first();
    }

    /**
     * @return Collection<int, array{metier: Metier, affinite: int, proximite: Proximite}>
     */
    private function tousLesMetiers(): Collection
    {
        return Metier::publies()
            ->avecProfil()
            ->with('secteur')
            ->withCount(['temoignages' => fn ($q) => $q->where('est_publie', true)])
            ->get()
            ->map(function (Metier $metier) {
                $correlation = $this->profil->correlationAvec($metier->profil());

                return [
                    'metier' => $metier,
                    'affinite' => $this->profil->affiniteAvec($metier->profil()),
                    'proximite' => Proximite::depuisCorrelation($correlation),
                    'correlation' => $correlation,
                ];
            })
            ->sortByDesc('correlation')
            ->values();
    }

    /**
     * Les filières qui mènent aux métiers recommandés, les plus citées d'abord.
     *
     * @param  Collection<int, array{metier: Metier}>  $metiersRecommandes
     * @return Collection<int, array{filiere: Filiere, metiers: Collection<int, Metier>}>
     */
    public function filieres(Collection $metiersRecommandes): Collection
    {
        // pluck() rend une collection simple : on repasse par une collection
        // Eloquent pour pouvoir charger la relation en une seule requête.
        $metiers = new EloquentCollection($metiersRecommandes->pluck('metier')->all());

        if ($metiers->isEmpty()) {
            return collect();
        }

        $metiers->load('filieres');

        return $metiers
            ->flatMap(fn (Metier $metier) => $metier->filieres->map(
                fn (Filiere $filiere) => ['filiere' => $filiere, 'metier' => $metier]
            ))
            ->groupBy(fn (array $lien) => $lien['filiere']->id)
            ->map(fn (Collection $liens) => [
                'filiere' => $liens->first()['filiere'],
                'metiers' => $liens->pluck('metier')->unique('id')->values(),
            ])
            ->sortByDesc(fn (array $entree) => $entree['metiers']->count())
            ->values();
    }
}
