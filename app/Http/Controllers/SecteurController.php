<?php

namespace App\Http\Controllers;

use App\Models\Secteur;
use Illuminate\View\View;

class SecteurController extends Controller
{
    /**
     * Les secteurs, porte d'entrée de la bibliothèque.
     */
    public function index(): View
    {
        return view('secteurs.index', [
            'secteurs' => Secteur::withCount(['metiers' => fn ($q) => $q->where('est_publie', true)])
                ->orderBy('ordre')
                ->get(),
        ]);
    }

    /**
     * Un secteur et les métiers publiés qu'il contient.
     */
    public function show(Secteur $secteur): View
    {
        $secteur->load([
            'metiers' => fn ($q) => $q->where('est_publie', true)->with('secteur')->orderBy('nom'),
        ]);

        return view('secteurs.show', [
            'secteur' => $secteur,
        ]);
    }
}
