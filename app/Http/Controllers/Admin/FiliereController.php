<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiliereRequest;
use App\Models\Filiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FiliereController extends Controller
{
    public function index(): View
    {
        return view('admin.filieres.index', [
            'filieres' => Filiere::withCount('metiers')->orderBy('nom')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.filieres.form', [
            'filiere' => new Filiere,
        ]);
    }

    public function store(FiliereRequest $request): RedirectResponse
    {
        $filiere = Filiere::create($request->validated());

        return redirect()
            ->route('admin.filieres.index')
            ->with('succes', "La filière « {$filiere->nom} » a été créée.");
    }

    public function edit(Filiere $filiere): View
    {
        return view('admin.filieres.form', [
            'filiere' => $filiere,
        ]);
    }

    public function update(FiliereRequest $request, Filiere $filiere): RedirectResponse
    {
        $filiere->update($request->validated());

        return redirect()
            ->route('admin.filieres.index')
            ->with('succes', "La filière « {$filiere->nom} » a été enregistrée.");
    }

    public function destroy(Filiere $filiere): RedirectResponse
    {
        $nom = $filiere->nom;
        $filiere->delete();

        return redirect()
            ->route('admin.filieres.index')
            ->with('succes', "La filière « {$nom} » a été supprimée.");
    }
}
