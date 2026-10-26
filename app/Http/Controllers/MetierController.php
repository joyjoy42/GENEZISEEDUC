<?php

namespace App\Http\Controllers;

use App\Models\Metier;
use App\Models\Secteur;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetierController extends Controller
{
    /**
     * La bibliothèque : tous les métiers publiés, filtrables par secteur.
     */
    public function index(Request $request): View
    {
        $recherche = $request->string('q')->trim()->value();
        $secteurChoisi = $request->string('secteur')->trim()->value();

        $metiers = Metier::query()
            ->publies()
            ->recherche($recherche)
            ->when($secteurChoisi !== '', function ($query) use ($secteurChoisi) {
                $query->whereHas('secteur', fn ($q) => $q->where('slug', $secteurChoisi));
            })
            ->with('secteur')
            ->withCount(['temoignages' => fn ($q) => $q->where('est_publie', true)])
            ->orderBy('nom')
            ->paginate(12)
            ->withQueryString();

        return view('metiers.index', [
            'metiers' => $metiers,
            'secteurs' => Secteur::withCount(['metiers' => fn ($q) => $q->where('est_publie', true)])
                ->orderBy('ordre')
                ->get(),
            'recherche' => $recherche,
            'secteurChoisi' => $secteurChoisi,
        ]);
    }

    /**
     * La fiche métier : le quotidien, les idées fausses, les interviews.
     */
    public function show(Metier $metier): View
    {
        abort_unless($metier->est_publie, 404);

        $metier->increment('vues');

        $metier->load([
            'secteur',
            'filieres',
            'temoignages' => fn ($q) => $q->where('est_publie', true)->latest('date_interview'),
        ]);

        $similaires = Metier::query()
            ->publies()
            ->where('secteur_id', $metier->secteur_id)
            ->whereKeyNot($metier->getKey())
            ->orderBy('nom')
            ->limit(3)
            ->get();

        return view('metiers.show', [
            'metier' => $metier,
            'similaires' => $similaires,
        ]);
    }
}
