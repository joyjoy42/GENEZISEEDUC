<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConnexionController extends Controller
{
    /**
     * Le formulaire de connexion de l'équipe.
     */
    public function afficher(): View
    {
        return view('auth.connexion');
    }

    /**
     * Ouvre la session si les identifiants sont bons.
     *
     * @throws ValidationException
     */
    public function connecter(Request $request): RedirectResponse
    {
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($identifiants, $request->boolean('se_souvenir'))) {
            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.tableau-bord'));
    }

    /**
     * Ferme la session et invalide le jeton.
     */
    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil');
    }
}
