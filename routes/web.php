<?php

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\Admin\FiliereController as AdminFiliereController;
use App\Http\Controllers\Admin\MetierController as AdminMetierController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\SecteurController as AdminSecteurController;
use App\Http\Controllers\Admin\TableauBordController;
use App\Http\Controllers\Admin\TemoignageController as AdminTemoignageController;
use App\Http\Controllers\CompteEleveController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\MetierController;
use App\Http\Controllers\SecteurController;
use App\Http\Controllers\TestOrientationController;
use Illuminate\Support\Facades\Route;

/*
 * Le site public : la bibliothèque des métiers.
 */
Route::get('/', [AccueilController::class, 'index'])->name('accueil');

Route::get('/metiers', [MetierController::class, 'index'])->name('metiers.index');
Route::get('/metiers/{metier}', [MetierController::class, 'show'])->name('metiers.show');

Route::get('/secteurs', [SecteurController::class, 'index'])->name('secteurs.index');
Route::get('/secteurs/{secteur}', [SecteurController::class, 'show'])->name('secteurs.show');

/*
 * Le test d'orientation. Les résultats sont anonymes et retrouvés par jeton.
 */
Route::get('/test', [TestOrientationController::class, 'intro'])->name('test.intro');
Route::get('/test/questions', [TestOrientationController::class, 'questionnaire'])->name('test.questions');
Route::post('/test', [TestOrientationController::class, 'calculer'])
    ->middleware('throttle:test')
    ->name('test.calculer');
Route::get('/test/resultat/{resultat}', [TestOrientationController::class, 'resultat'])->name('test.resultat');

/*
 * Les comptes élèves : garder ses résultats de test et les retrouver.
 * Garde séparé de celui de l'équipe : une session élève n'ouvre pas /admin.
 */
Route::middleware('guest:eleve')->group(function () {
    Route::get('/inscription', [CompteEleveController::class, 'formulaireInscription'])->name('eleves.inscription');
    Route::post('/inscription', [CompteEleveController::class, 'inscrire'])
        ->middleware('throttle:connexion')
        ->name('eleves.inscrire');

    Route::get('/mon-compte', [CompteEleveController::class, 'formulaireConnexion'])->name('eleves.connexion');
    Route::post('/mon-compte', [CompteEleveController::class, 'connecter'])
        ->middleware('throttle:connexion')
        ->name('eleves.connecter');
});

Route::middleware('auth:eleve')->group(function () {
    Route::get('/mon-suivi', [CompteEleveController::class, 'suivi'])->name('eleves.suivi');
    Route::post('/mon-compte/deconnexion', [CompteEleveController::class, 'deconnecter'])->name('eleves.deconnexion');
});

/*
 * La connexion de l'équipe. Pas d'inscription : les comptes sont créés
 * en ligne de commande avec `php artisan genezise:administrateur`.
 */
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'afficher'])->name('login');
    Route::post('/connexion', [ConnexionController::class, 'connecter'])
        ->middleware('throttle:connexion')
        ->name('connexion');
});

Route::post('/deconnexion', [ConnexionController::class, 'deconnecter'])
    ->middleware('auth:web')
    ->name('deconnexion');

/*
 * Le back-office : saisie du contenu de la bibliothèque.
 */
Route::middleware('auth:web')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', TableauBordController::class)->name('tableau-bord');

    Route::resource('metiers', AdminMetierController::class)->except('show');
    Route::resource('secteurs', AdminSecteurController::class)->except('show');
    Route::resource('filieres', AdminFiliereController::class)->except('show');
    Route::resource('temoignages', AdminTemoignageController::class)->except('show');
    Route::resource('questions', AdminQuestionController::class)->except('show');
});
