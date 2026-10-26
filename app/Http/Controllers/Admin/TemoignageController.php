<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TemoignageRequest;
use App\Models\Metier;
use App\Models\Temoignage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemoignageController extends Controller
{
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
        ]);
    }

    public function store(TemoignageRequest $request): RedirectResponse
    {
        $temoignage = Temoignage::create($request->validated());

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', "L'interview de {$temoignage->nom} a été enregistrée.");
    }

    public function edit(Temoignage $temoignage): View
    {
        return view('admin.temoignages.form', [
            'temoignage' => $temoignage,
            'metiers' => Metier::orderBy('nom')->get(),
        ]);
    }

    public function update(TemoignageRequest $request, Temoignage $temoignage): RedirectResponse
    {
        $temoignage->update($request->validated());

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', "L'interview de {$temoignage->nom} a été enregistrée.");
    }

    public function destroy(Temoignage $temoignage): RedirectResponse
    {
        $nom = $temoignage->nom;
        $temoignage->delete();

        return redirect()
            ->route('admin.temoignages.index')
            ->with('succes', "L'interview de {$nom} a été supprimée.");
    }
}
