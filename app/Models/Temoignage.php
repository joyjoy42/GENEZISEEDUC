<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Temoignage extends Model
{
    use HasFactory;

    protected $fillable = [
        'metier_id', 'nom', 'fonction', 'entreprise', 'ville', 'citation',
        'video_url', 'plateforme', 'fichier_video', 'duree_secondes',
        'date_interview', 'est_publie',
    ];

    protected function casts(): array
    {
        return [
            'date_interview' => 'date',
            'est_publie' => 'boolean',
        ];
    }

    public function metier(): BelongsTo
    {
        return $this->belongsTo(Metier::class);
    }

    public function scopePublies(Builder $query): Builder
    {
        return $query->where('est_publie', true);
    }
}
