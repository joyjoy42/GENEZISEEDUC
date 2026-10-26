<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un élève ou un étudiant qui garde ses résultats de test.
 *
 * Le mot de passe n'est pas ici : Supabase Auth le détient et le vérifie.
 * Cette table ne sert qu'à relier des résultats à quelqu'un.
 */
class Eleve extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory;

    /**
     * L'âge à partir duquel on accepte de créer un compte.
     *
     * En dessous, le test reste anonyme : la charte Joy's world évite déjà de
     * collecter des données personnelles d'élèves mineurs, et un enfant de 6e
     * ne peut pas consentir seul.
     */
    public const AGE_MINIMUM = 15;

    protected $fillable = ['supabase_id', 'email', 'annee_naissance'];

    public function resultats(): HasMany
    {
        return $this->hasMany(ResultatTest::class);
    }

    /**
     * L'âge atteint dans l'année : on ne demande que l'année de naissance,
     * pas une date complète, qui serait une donnée personnelle de plus.
     */
    public function age(): int
    {
        return (int) date('Y') - $this->annee_naissance;
    }
}
