<?php

namespace App\Models;

use App\Orientation\Dimension;
use App\Orientation\Profil;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Le résultat d'un test, conservé sans aucune donnée personnelle : seulement
 * les six scores et un jeton qui permet de retrouver sa page.
 */
class ResultatTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeton', 'nombre_reponses',
        'score_realiste', 'score_investigateur', 'score_artistique',
        'score_social', 'score_entreprenant', 'score_conventionnel',
    ];

    public static function enregistrer(Profil $profil, int $nombreReponses): self
    {
        return self::create([
            'jeton' => (string) Str::ulid(),
            'nombre_reponses' => $nombreReponses,
            ...$profil->enColonnes(),
        ]);
    }

    public function profil(): Profil
    {
        $scores = [];

        foreach (Dimension::cases() as $dimension) {
            $scores[$dimension->value] = (int) $this->{$dimension->colonne()};
        }

        return Profil::depuisTableau($scores);
    }

    public function getRouteKeyName(): string
    {
        return 'jeton';
    }
}
