<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'slug', 'niveau', 'duree_annees', 'description', 'etablissements',
        'formation_disponible_pays', 'financements',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'formation_disponible_pays' => 'boolean',
        ];
    }

    public function metiers(): BelongsToMany
    {
        return $this->belongsToMany(Metier::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
