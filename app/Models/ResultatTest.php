<?php

namespace App\Models;

use App\Orientation\Dimension;
use App\Orientation\Profil;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Le résultat d'un test : six scores et un jeton qui permet de retrouver sa
 * page.
 *
 * Il reste anonyme par défaut. `eleve_id` n'est rempli que si la personne a
 * choisi de créer un compte pour garder ses résultats.
 */
class ResultatTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeton', 'nombre_reponses', 'eleve_id',
        'score_realiste', 'score_investigateur', 'score_artistique',
        'score_social', 'score_entreprenant', 'score_conventionnel',
    ];

    public static function enregistrer(Profil $profil, int $nombreReponses, ?Eleve $eleve = null): self
    {
        return self::create([
            'jeton' => (string) Str::ulid(),
            'nombre_reponses' => $nombreReponses,
            'eleve_id' => $eleve?->id,
            ...$profil->enColonnes(),
        ]);
    }

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
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
