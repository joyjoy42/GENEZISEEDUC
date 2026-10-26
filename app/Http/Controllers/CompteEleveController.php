<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use App\Models\ResultatTest;
use App\Supabase\ComptesEleves;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Les comptes élèves : s'inscrire, se connecter, retrouver ses résultats.
 *
 * Le back-office a sa propre porte (ConnexionController) et son propre
 * garde : une session élève ne l'ouvre pas.
 */
class CompteEleveController extends Controller
{
    public function __construct(private ComptesEleves $comptes) {}

    public function formulaireInscription(): View
    {
        return view('eleves.inscription');
    }

    public function inscrire(Request $request): RedirectResponse
    {
        $anneeMaximale = (int) date('Y') - Eleve::AGE_MINIMUM;

        $donnees = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('eleves', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'annee_naissance' => ['required', 'integer', 'min:1940', 'max:'.$anneeMaximale],
        ], [
            'annee_naissance.max' => 'Il faut avoir '.Eleve::AGE_MINIMUM
                .' ans pour créer un compte. Tu peux passer le test sans compte : garde le lien de ton résultat.',
            'email.unique' => 'Un compte existe déjà avec cette adresse.',
        ], [
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
            'annee_naissance' => 'année de naissance',
        ]);

        $resultat = $this->comptes->inscrire($donnees['email'], $donnees['password']);

        if (isset($resultat['erreur'])) {
            throw ValidationException::withMessages(['email' => $resultat['erreur']]);
        }

        $eleve = Eleve::create([
            'supabase_id' => $resultat['id'],
            'email' => $donnees['email'],
            'annee_naissance' => $donnees['annee_naissance'],
        ]);

        return $this->ouvreLaSession($request, $eleve);
    }

    public function formulaireConnexion(): View
    {
        return view('eleves.connexion');
    }

    public function connecter(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $supabaseId = $this->comptes->verifie($donnees['email'], $donnees['password']);
        $eleve = $supabaseId ? Eleve::where('supabase_id', $supabaseId)->first() : null;

        if (! $eleve) {
            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        return $this->ouvreLaSession($request, $eleve);
    }

    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::guard('eleve')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil');
    }

    /**
     * Les résultats de test rattachés à ce compte, du plus récent au plus
     * ancien.
     */
    public function suivi(Request $request): View
    {
        return view('eleves.suivi', [
            'resultats' => $request->user('eleve')->resultats()->latest()->get(),
        ]);
    }

    public function confirmationSuppression(): View
    {
        return view('eleves.suppression');
    }

    /**
     * Efface le compte, chez Supabase puis ici.
     *
     * Dans cet ordre : si Supabase refuse, rien n'est perdu et l'élève peut
     * réessayer. L'inverse laisserait un compte fantôme chez Supabase, avec
     * une adresse prise que personne ne pourrait plus réutiliser.
     *
     * Les résultats de test, eux, ne sont pas supprimés : ils redeviennent
     * anonymes, c'est-à-dire ce qu'ils étaient avant la création du compte.
     */
    public function supprimer(Request $request): RedirectResponse
    {
        $eleve = $request->user('eleve');

        if (! $this->comptes->supprime($eleve->supabase_id)) {
            return back()->withErrors([
                'suppression' => 'La suppression a échoué. Ton compte est intact : réessaie dans un moment.',
            ]);
        }

        Auth::guard('eleve')->logout();
        $eleve->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil')
            ->with('succes', 'Ton compte est supprimé. Tes résultats de test restent anonymes.');
    }

    private function ouvreLaSession(Request $request, Eleve $eleve): RedirectResponse
    {
        Auth::guard('eleve')->login($eleve, remember: true);

        $request->session()->regenerate();

        $this->rattacheLeDernierResultat($request, $eleve);

        return redirect()->intended(route('eleves.suivi'));
    }

    /**
     * Si l'élève vient de passer le test avant de créer son compte, son
     * résultat le suit : sinon il le perdrait au moment même où il s'inscrit
     * pour le garder.
     */
    private function rattacheLeDernierResultat(Request $request, Eleve $eleve): void
    {
        $jeton = $request->session()->pull('dernier_resultat');

        if ($jeton) {
            ResultatTest::where('jeton', $jeton)
                ->whereNull('eleve_id')
                ->update(['eleve_id' => $eleve->id]);
        }
    }
}
