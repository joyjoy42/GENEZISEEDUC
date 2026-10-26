<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SecteurRequest;
use App\Models\Secteur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SecteurController extends Controller
{
    public function index(): View
    {
        return view('admin.secteurs.index', [
            'secteurs' => Secteur::withCount('metiers')->orderBy('ordre')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.secteurs.form', [
            'secteur' => new Secteur(['couleur' => '#1b1f3b', 'ordre' => 0]),
        ]);
    }

    public function store(SecteurRequest $request): RedirectResponse
    {
        $secteur = Secteur::create($request->validated());

        return redirect()
            ->route('admin.secteurs.index')
            ->with('succes', "Le secteur « {$secteur->nom} » a été créé.");
    }

    public function edit(Secteur $secteur): View
    {
        return view('admin.secteurs.form', [
            'secteur' => $secteur,
        ]);
    }

    public function update(SecteurRequest $request, Secteur $secteur): RedirectResponse
    {
        $secteur->update($request->validated());

        return redirect()
            ->route('admin.secteurs.index')
            ->with('succes', "Le secteur « {$secteur->nom} » a été enregistré.");
    }

    /**
     * On refuse la suppression d'un secteur qui contient encore des métiers,
     * sinon la cascade emporterait les fiches avec lui.
     */
    public function destroy(Secteur $secteur): RedirectResponse
    {
        if ($secteur->metiers()->exists()) {
            return redirect()
                ->route('admin.secteurs.index')
                ->withErrors("Impossible de supprimer « {$secteur->nom} » : des métiers y sont encore rattachés.");
        }

        $nom = $secteur->nom;
        $secteur->delete();

        return redirect()
            ->route('admin.secteurs.index')
            ->with('succes', "Le secteur « {$nom} » a été supprimé.");
    }
}
