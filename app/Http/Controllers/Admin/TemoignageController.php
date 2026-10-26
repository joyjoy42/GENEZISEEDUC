<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TemoignageRequest;
use App\Models\Metier;
use App\Models\Temoignage;
use App\Supabase\StockageMedias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class TemoignageController extends Controller
{
    public function __construct(private StockageMedias $stockage) {}

    public function index(): View
    {
        return view('admin.temoignages.index', [
            'temoignages' => Temoignage::with('metier')
                ->latest('date_interview')
                ->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.temoignages.form', [
            'temoignage' => new Temoignage([
                'ville' => 'Cotonou',
                'metier_id' => $request->integer('metier') ?: null,
            ]),
            'metiers' => Metier::orderBy('nom')->get(),
            'lienMedia' => null,
        ]);
    }

    public function store(TemoignageRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        unset($donnees['media']);

        $temoignage = Temoignage::create($donnees);

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', $this->message($request, $temoignage));
    }

    public function edit(Temoignage $temoignage): View
    {
        return view('admin.temoignages.form', [
            'temoignage' => $temoignage,
            'metiers' => Metier::orderBy('nom')->get(),
            'lienMedia' => $this->stockage->lienSigne($temoignage->fichier_video),
        ]);
    }

    public function update(TemoignageRequest $request, Temoignage $temoignage): RedirectResponse
    {
        $donnees = $request->validated();
        unset($donnees['media']);

        $temoignage->update($donnees);

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', $this->message($request, $temoignage));
    }

    public function destroy(Temoignage $temoignage): RedirectResponse
    {
        $nom = $temoignage->nom;
        $this->stockage->supprime($temoignage->fichier_video);
        $temoignage->delete();

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', "L'interview de {$nom} a été supprimée.");
    }

    private function message(TemoignageRequest $request, Temoignage $temoignage): string
    {
        $message = "L'interview de {$temoignage->nom} a été enregistrée.";

        if ($request->hasFile('media')) {
            $message .= $this->rangeLeMedia($request->file('media'), $temoignage);
        }

        return $message;
    }

    /**
     * Envoie le média dans le bucket privé et le rattache à l'interview.
     *
     * L'ancien fichier est supprimé : sans ça, chaque correction laisserait un
     * rush orphelin que plus rien ne référence.
     */
    private function rangeLeMedia(UploadedFile $media, Temoignage $temoignage): string
    {
        $ancien = $temoignage->fichier_video;
        $chemin = $this->stockage->televerse($media, 'temoignages');

        if ($chemin === null) {
            return " Mais l'envoi du média a échoué : vérifie la clé Supabase, puis réessaie.";
        }

        $temoignage->update(['fichier_video' => $chemin]);

        if ($ancien !== null && $ancien !== $chemin) {
            $this->stockage->supprime($ancien);
        }

        return ' Le média a été envoyé.';
    }
}
