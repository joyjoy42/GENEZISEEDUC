<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReponsesTestRequest;
use App\Models\Question;
use App\Models\ResultatTest;
use App\Orientation\Dimension;
use App\Orientation\Profil;
use App\Orientation\Recommandeur;
use App\Orientation\Reponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestOrientationController extends Controller
{
    /**
     * La page d'entrée : ce que le test fait, et surtout ce qu'il ne fait pas.
     */
    public function intro(): View
    {
        return view('test.intro', [
            'nombreQuestions' => Question::actives()->count(),
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function questionnaire(): View
    {
        return view('test.questions', [
            'questions' => Question::actives()->orderBy('ordre')->get(),
            'reponses' => Reponse::cases(),
        ]);
    }

    /**
     * Calcule le profil, le conserve sans donnée personnelle, et redirige vers
     * une page de résultat partageable.
     */
    public function calculer(ReponsesTestRequest $request): RedirectResponse
    {
        $questions = Question::actives()->get();

        /** @var array<int, int> $reponses */
        $reponses = $request->validated()['reponses'];

        $profil = Profil::depuisReponses($reponses, $questions);
        $resultat = ResultatTest::enregistrer($profil, count($reponses));

        return redirect()->route('test.resultat', $resultat);
    }

    public function resultat(ResultatTest $resultat): View
    {
        $profil = $resultat->profil();
        $recommandeur = new Recommandeur($profil);

        $metiers = $recommandeur->metiers();
        $deSecours = false;

        // Personne ne doit repartir les mains vides : si rien ne ressort
        // nettement, on montre quand même les plus proches, en le disant.
        if (! $profil->estPlat() && $metiers->isEmpty()) {
            $metiers = collect(array_filter([$recommandeur->lePlusProche()]));
            $deSecours = $metiers->isNotEmpty();
        }

        return view('test.resultat', [
            'resultat' => $resultat,
            'profil' => $profil,
            'profilPlat' => $profil->estPlat(),
            'dimensions' => $this->dimensionsTriees($profil),
            'metiers' => $metiers,
            'deSecours' => $deSecours,
            'filieres' => $recommandeur->filieres($metiers),
        ]);
    }

    /**
     * Les six dimensions de la plus forte à la plus faible.
     *
     * @return array<int, Dimension>
     */
    private function dimensionsTriees(Profil $profil): array
    {
        return collect(Dimension::cases())
            ->sortByDesc(fn (Dimension $dimension) => $profil->score($dimension))
            ->values()
            ->all();
    }
}
