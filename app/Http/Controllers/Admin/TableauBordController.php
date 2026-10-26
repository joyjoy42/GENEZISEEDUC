<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Metier;
use App\Models\Question;
use App\Models\ResultatTest;
use App\Models\Secteur;
use App\Models\Temoignage;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    /**
     * L'état de la bibliothèque en un coup d'œil, et ce qui reste à remplir.
     */
    public function __invoke(): View
    {
        return view('admin.tableau-bord', [
            'nombreMetiers' => Metier::count(),
            'nombreMetiersPublies' => Metier::publies()->count(),
            'nombreTemoignages' => Temoignage::count(),
            'nombreTemoignagesPublies' => Temoignage::publies()->count(),
            'nombreSecteurs' => Secteur::count(),
            'nombreFilieres' => Filiere::count(),
            'metiersSansInterview' => Metier::publies()
                ->doesntHave('temoignages')
                ->orderBy('nom')
                ->get(),
            'metiersRecents' => Metier::with('secteur')->latest('updated_at')->limit(5)->get(),
            'nombreQuestions' => Question::actives()->count(),
            'nombreTests' => ResultatTest::count(),
            'metiersSansProfil' => Metier::publies()->sansProfil()->orderBy('nom')->get(),
        ]);
    }
}
