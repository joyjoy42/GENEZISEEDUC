<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetierRequest;
use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Secteur;
use App\Orientation\Dimension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetierController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = $request->string('q')->trim()->value();

        return view('admin.metiers.index', [
            'metiers' => Metier::query()
                ->recherche($recherche)
                ->with('secteur')
                ->withCount('temoignages')
                ->orderBy('nom')
                ->paginate(20)
                ->withQueryString(),
            'recherche' => $recherche,
        ]);
    }

    public function create(): View
    {
        return view('admin.metiers.form', [
            'metier' => new Metier,
            'secteurs' => Secteur::orderBy('ordre')->get(),
            'filieres' => Filiere::orderBy('nom')->get(),
            'filieresChoisies' => [],
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function store(MetierRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $filieres = $donnees['filieres'] ?? [];
        unset($donnees['filieres']);

        $donnees['publie_le'] = $donnees['est_publie'] ? now() : null;

        $metier = Metier::create($donnees);
        $metier->filieres()->sync($filieres);

        return redirect()
            ->route('admin.metiers.index')
            ->with('succes', "Le métier « {$metier->nom} » a été créé.");
    }

    public function edit(Metier $metier): View
    {
        return view('admin.metiers.form', [
            'metier' => $metier,
            'secteurs' => Secteur::orderBy('ordre')->get(),
            'filieres' => Filiere::orderBy('nom')->get(),
            'filieresChoisies' => $metier->filieres()->pluck('filieres.id')->all(),
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function update(MetierRequest $request, Metier $metier): RedirectResponse
    {
        $donnees = $request->validated();
        $filieres = $donnees['filieres'] ?? [];
        unset($donnees['filieres']);

        $donnees['publie_le'] = $this->datePublication($metier, $donnees['est_publie']);

        $metier->update($donnees);
        $metier->filieres()->sync($filieres);

        return redirect()
            ->route('admin.metiers.index')
            ->with('succes', "Le métier « {$metier->nom} » a été enregistré.");
    }

    public function destroy(Metier $metier): RedirectResponse
    {
        $nom = $metier->nom;
        $metier->delete();

        return redirect()
            ->route('admin.metiers.index')
            ->with('succes', "Le métier « {$nom} » a été supprimé.");
    }

    /**
     * On garde la date de première publication quand la fiche était déjà publiée,
     * et on la remet à zéro si elle repasse en brouillon.
     */
    private function datePublication(Metier $metier, bool $estPublie): mixed
    {
        if (! $estPublie) {
            return null;
        }

        return $metier->publie_le ?? now();
    }
}
