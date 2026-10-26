<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les en-têtes que le navigateur attend d'un site sérieux.
 *
 * Pas de Content-Security-Policy ici : les styles sont écrits en attributs
 * `style="…"` et le back-office utilise des `onsubmit` en ligne, qu'une CSP
 * stricte casserait. La poser demandera d'abord de sortir ce code des pages.
 */
class EnTetesDeSecurite
{
    public function handle(Request $request, Closure $next): Response
    {
        $reponse = $next($request);

        $reponse->headers->set('X-Content-Type-Options', 'nosniff');
        $reponse->headers->set('X-Frame-Options', 'DENY');
        $reponse->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $reponse->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS seulement sur une vraie connexion chiffrée : l'envoyer en HTTP
        // ne sert à rien, et l'envoyer en développement bloque localhost.
        if ($request->secure()) {
            $reponse->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $reponse;
    }
}
