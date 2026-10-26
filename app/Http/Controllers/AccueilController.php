<?php

namespace App\Http\Controllers;

use App\Models\Metier;
use App\Models\Secteur;
use App\Models\Temoignage;
use Illuminate\View\View;

class AccueilController extends Controller
{
    /**
     * La page d'accueil : la promesse, les secteurs, les derniers métiers publiés.
     */
    public function index(): View
    {
        return view('accueil', [
            'secteurs' => Secteur::withCount(['metiers' => fn ($q) => $q->where('est_publie', true)])
                ->orderBy('ordre')
                ->get(),
            'derniersMetiers' => Metier::publies()
                ->with('secteur')
                ->latest('publie_le')
                ->limit(3)
                ->get(),
            'nombreMetiers' => Metier::publies()->count(),
            'nombreTemoignages' => Temoignage::publies()->count(),
        ]);
    }
}
