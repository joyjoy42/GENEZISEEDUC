<?php

namespace App\Supabase;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Les médias d'interviews — rushes vidéo, notes vocales, photos d'atelier —
 * vivent dans un bucket privé Supabase.
 *
 * Privé, parce que ces fichiers ne sortent que sous consentement écrit et
 * qu'ils montrent des lieux de travail et des personnes identifiables. Rien
 * n'est lisible sans un lien signé, qui expire.
 */
class StockageMedias
{
    public function __construct(
        private string $url,
        private string $cleSecrete,
        private string $bucket,
    ) {}

    /**
     * Sans clé secrète, l'application tourne sans stockage distant plutôt que
     * de planter : c'est le cas en test et sur un poste fraîchement cloné.
     */
    public function estConfigure(): bool
    {
        return $this->url !== '' && $this->cleSecrete !== '';
    }

    /**
     * Envoie un fichier et renvoie son chemin dans le bucket, ou null si
     * l'envoi a échoué.
     */
    public function televerse(UploadedFile $fichier, string $dossier): ?string
    {
        if (! $this->estConfigure()) {
            return null;
        }

        $chemin = $this->chemin($fichier, $dossier);

        // Le fichier est envoyé en flux : un rush de 300 Mo ne doit pas passer
        // par la mémoire de PHP.
        $flux = fopen($fichier->getRealPath(), 'rb');

        $reponse = $this->requete()
            ->withHeaders([
                'Content-Type' => $fichier->getMimeType() ?: 'application/octet-stream',
                'x-upsert' => 'true',
            ])
            ->timeout(600)
            ->send('POST', $this->pointDAcces('object/'.$this->bucket.'/'.$chemin), ['body' => $flux]);

        if ($reponse->failed()) {
            Log::error('Supabase Storage : envoi refusé.', [
                'chemin' => $chemin,
                'statut' => $reponse->status(),
                'reponse' => $reponse->body(),
            ]);

            return null;
        }

        return $chemin;
    }

    /**
     * Lien de lecture temporaire. Le bucket étant privé, c'est le seul moyen
     * d'ouvrir un média, et le lien cesse de fonctionner après $secondes.
     */
    public function lienSigne(?string $chemin, int $secondes = 3600): ?string
    {
        if (blank($chemin) || ! $this->estConfigure()) {
            return null;
        }

        $reponse = $this->requete()
            ->post($this->pointDAcces('object/sign/'.$this->bucket.'/'.$chemin), [
                'expiresIn' => $secondes,
            ]);

        if ($reponse->failed()) {
            return null;
        }

        $lien = $reponse->json('signedURL') ?? $reponse->json('signedUrl');

        return $lien ? rtrim($this->url, '/').'/storage/v1'.$lien : null;
    }

    /**
     * Supprime un média, par exemple quand on en envoie un nouveau à la place.
     */
    public function supprime(?string $chemin): bool
    {
        if (blank($chemin) || ! $this->estConfigure()) {
            return false;
        }

        return $this->requete()
            ->delete($this->pointDAcces('object/'.$this->bucket.'/'.$chemin))
            ->successful();
    }

    /**
     * Une clé `sb_secret_…` n'est pas un JWT : Storage la refuse en
     * Authorization seul (« Invalid Compact JWS ») et attend l'en-tête
     * `apikey`. On envoie les deux, ce qui marche aussi avec une ancienne
     * clé `service_role`.
     */
    private function requete(): PendingRequest
    {
        return Http::withHeaders([
            'apikey' => $this->cleSecrete,
            'Authorization' => 'Bearer '.$this->cleSecrete,
        ]);
    }

    /**
     * Un nom de fichier sans accent ni espace, rangé par année, et suffixé
     * pour que deux rushes du même jour ne s'écrasent pas.
     */
    private function chemin(UploadedFile $fichier, string $dossier): string
    {
        $nom = Str::slug(pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = Str::lower($fichier->getClientOriginalExtension() ?: 'bin');

        return sprintf('%s/%s/%s-%s.%s', trim($dossier, '/'), date('Y'), $nom ?: 'media', Str::random(8), $extension);
    }

    private function pointDAcces(string $chemin): string
    {
        return rtrim($this->url, '/').'/storage/v1/'.$chemin;
    }
}
