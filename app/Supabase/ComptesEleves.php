<?php

namespace App\Supabase;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Supabase Auth détient les mots de passe des élèves ; l'application ne les
 * voit que le temps de les lui transmettre et n'en garde rien.
 *
 * Les comptes sont créés par l'API d'administration, qui les marque confirmés
 * tout de suite. Le parcours par e-mail de confirmation renvoie son jeton
 * dans le fragment de l'URL, que le serveur ne reçoit jamais : il demanderait
 * du JavaScript et un SMTP, pour vérifier une adresse dont la plateforme n'a
 * aujourd'hui aucun usage.
 *
 * shortcut: aucune vérification d'adresse e-mail, donc n'importe quelle
 * adresse peut être revendiquée ; à reprendre le jour où un e-mail sera
 * envoyé aux élèves (alerte bourse, rappel d'inscription).
 */
class ComptesEleves
{
    public function __construct(
        private string $url,
        private string $cleSecrete,
    ) {}

    public function estConfigure(): bool
    {
        return $this->url !== '' && $this->cleSecrete !== '';
    }

    /**
     * Crée le compte et renvoie l'identifiant Supabase, ou null.
     *
     * @return array{id: string}|array{erreur: string}
     */
    public function inscrire(string $email, string $motDePasse): array
    {
        if (! $this->estConfigure()) {
            return ['erreur' => "La création de comptes n'est pas configurée."];
        }

        $reponse = $this->requete()->post($this->url.'/auth/v1/admin/users', [
            'email' => $email,
            'password' => $motDePasse,
            'email_confirm' => true,
        ]);

        if ($reponse->successful() && $reponse->json('id')) {
            return ['id' => (string) $reponse->json('id')];
        }

        // Supabase renvoie 422 quand l'adresse existe déjà.
        if ($reponse->status() === 422) {
            return ['erreur' => 'Un compte existe déjà avec cette adresse.'];
        }

        Log::error('Supabase Auth : inscription refusée.', [
            'statut' => $reponse->status(),
            'reponse' => $reponse->body(),
        ]);

        return ['erreur' => 'La création du compte a échoué. Réessaie dans un moment.'];
    }

    /**
     * Vérifie un couple adresse / mot de passe et renvoie l'identifiant
     * Supabase, ou null si le compte n'existe pas ou le mot de passe est faux.
     */
    public function verifie(string $email, string $motDePasse): ?string
    {
        if (! $this->estConfigure()) {
            return null;
        }

        $reponse = $this->requete()->post($this->url.'/auth/v1/token?grant_type=password', [
            'email' => $email,
            'password' => $motDePasse,
        ]);

        return $reponse->successful() ? $reponse->json('user.id') : null;
    }

    /**
     * Supprime le compte chez Supabase, par exemple quand l'élève efface le
     * sien : sans ça, l'adresse resterait prise.
     */
    public function supprime(string $supabaseId): bool
    {
        if (! $this->estConfigure()) {
            return false;
        }

        return $this->requete()->delete($this->url.'/auth/v1/admin/users/'.$supabaseId)->successful();
    }

    /**
     * Une clé `sb_secret_…` n'est pas un JWT : les services Supabase la
     * refusent en Authorization seul et attendent l'en-tête `apikey`.
     */
    private function requete(): PendingRequest
    {
        return Http::withHeaders([
            'apikey' => $this->cleSecrete,
            'Authorization' => 'Bearer '.$this->cleSecrete,
        ])->timeout(30);
    }
}
