<?php

namespace App\Providers;

use App\Supabase\ComptesEleves;
use App\Supabase\StockageMedias;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ComptesEleves::class, fn () => new ComptesEleves(
            (string) config('supabase.url'),
            (string) config('supabase.secret_key'),
        ));

        $this->app->singleton(StockageMedias::class, fn () => new StockageMedias(
            (string) config('supabase.url'),
            (string) config('supabase.secret_key'),
            (string) config('supabase.storage.bucket_interviews'),
        ));
    }

    public function boot(): void
    {
        $this->forcerHttps();
        $this->signalerLesRequetesParesseuses();
        $this->limiterLesRequetes();
    }

    /**
     * En production, toutes les URL générées pointent vers HTTPS.
     *
     * Sans ça, un lien en http:// suffit à faire circuler en clair le mot de
     * passe d'un membre de l'équipe.
     */
    private function forcerHttps(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Hors production, une relation chargée à la volée lève une exception :
     * c'est la façon la plus simple de voir un N+1 avant qu'il parte en ligne.
     */
    private function signalerLesRequetesParesseuses(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
    }

    /**
     * Les deux points d'entrée ouverts au public.
     */
    private function limiterLesRequetes(): void
    {
        // Le formulaire de connexion est la seule porte du back-office :
        // on compte par e-mail autant que par adresse IP.
        RateLimiter::for('connexion', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);

        // Le test s'enregistre sans compte. On compte d'abord par session :
        // une classe entière dans un lycée ou un cybercafé partage une seule
        // adresse IP, et une limite par IP seule bloquerait le 7e élève.
        // Le plafond par IP ne sert qu'à arrêter un envoi automatisé.
        RateLimiter::for('test', fn (Request $request) => [
            Limit::perMinute(5)->by('session:'.$request->session()->getId()),
            Limit::perMinute(30)->by($request->ip()),
        ]);
    }
}
