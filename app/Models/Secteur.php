<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secteur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'slug', 'description', 'couleur', 'ordre'];

    public function metiers(): HasMany
    {
        return $this->hasMany(Metier::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Le nom du secteur fait partie du texte de recherche de ses métiers :
     * s'il change, il faut le réécrire dans chaque fiche.
     */
    protected static function booted(): void
    {
        static::saved(function (self $secteur) {
            if ($secteur->wasChanged('nom')) {
                $secteur->metiers()->each(fn (Metier $metier) => $metier->save());
            }
        });
    }
}
