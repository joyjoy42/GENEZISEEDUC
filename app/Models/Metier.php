<?php

namespace App\Models;

use App\Orientation\Dimension;
use App\Orientation\Profil;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Metier extends Model
{
    use HasFactory;

    protected $fillable = [
        'secteur_id', 'nom', 'slug', 'accroche', 'le_quotidien', 'competences', 'outils',
        'salaire_min', 'salaire_max', 'devise', 'ou_travailler', 'debouches',
        'idee_fausse', 'realite', 'niveau_etude', 'image', 'est_publie', 'publie_le',
        'score_realiste', 'score_investigateur', 'score_artistique',
        'score_social', 'score_entreprenant', 'score_conventionnel',
    ];

    protected function casts(): array
    {
        return [
            'competences' => 'array',
            'outils' => 'array',
            'est_publie' => 'boolean',
            'publie_le' => 'datetime',
        ];
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class);
    }

    public function temoignages(): HasMany
    {
        return $this->hasMany(Temoignage::class);
    }

    public function filieres(): BelongsToMany
    {
        return $this->belongsToMany(Filiere::class);
    }

    public function scopePublies(Builder $query): Builder
    {
        return $query->where('est_publie', true);
    }

    /**
     * Les métiers dont le profil RIASEC a été renseigné : sans lui, le métier
     * ne peut être recommandé à personne.
     */
    public function scopeAvecProfil(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            foreach (Dimension::cases() as $dimension) {
                $q->orWhere($dimension->colonne(), '>', 0);
            }
        });
    }

    /**
     * Les métiers dont les six notes sont restées à zéro.
     */
    public function scopeSansProfil(Builder $query): Builder
    {
        foreach (Dimension::cases() as $dimension) {
            $query->where($dimension->colonne(), 0);
        }

        return $query;
    }

    public function profil(): Profil
    {
        return Profil::depuisMetier($this);
    }

    /**
     * Recherche libre, insensible aux accents et à la casse.
     *
     * On compare sur `texte_recherche`, la copie normalisée de la fiche, pour
     * que « mecanicien » trouve « Mécanicien automobile ».
     */
    public function scopeRecherche(Builder $query, ?string $terme): Builder
    {
        if (blank($terme)) {
            return $query;
        }

        // `%` et `_` sont des jokers SQL : sans échappement, chercher « _ »
        // remonte toute la bibliothèque. La clause ESCAPE est indispensable,
        // le simple antislash ne suffit pas.
        $motif = '%'.preg_replace('/([\\\\%_])/', '\\\\$1', self::normaliser($terme)).'%';

        return $query->whereRaw("texte_recherche LIKE ? ESCAPE '\\'", [$motif]);
    }

    /**
     * Minuscules et sans accents : « Mécanicien » devient « mecanicien ».
     */
    public static function normaliser(?string $texte): string
    {
        return Str::lower(Str::ascii((string) $texte));
    }

    /**
     * Le texte sur lequel porte la recherche.
     *
     * Le nom du secteur en fait partie : un élève cherche « mécanique » ou
     * « santé » bien avant de connaître l'intitulé exact d'un métier.
     */
    public function texteRecherchable(): string
    {
        return self::normaliser(implode(' ', array_filter([
            $this->nom,
            $this->accroche,
            $this->le_quotidien,
            $this->idee_fausse,
            $this->realite,
            $this->niveau_etude,
            // Requête directe plutôt que $this->secteur : lire la relation
            // ici déclencherait un chargement paresseux, interdit hors
            // production, et l'enregistrement planterait.
            Secteur::whereKey($this->secteur_id)->value('nom'),
        ])));
    }

    protected static function booted(): void
    {
        static::saving(function (self $metier) {
            $metier->texte_recherche = $metier->texteRecherchable();
        });
    }

    /**
     * Fourchette de salaire lisible, ou null si on ne l'a pas encore mesurée.
     */
    public function fourchetteSalaire(): ?string
    {
        if ($this->salaire_min === null && $this->salaire_max === null) {
            return null;
        }

        $format = fn (int $montant) => number_format($montant, 0, ',', ' ');

        if ($this->salaire_min !== null && $this->salaire_max !== null) {
            return $format($this->salaire_min).' – '.$format($this->salaire_max).' '.$this->devise;
        }

        $montant = $this->salaire_min ?? $this->salaire_max;
        $prefixe = $this->salaire_min !== null ? 'à partir de ' : "jusqu'à ";

        return $prefixe.$format($montant).' '.$this->devise;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
