<?php

namespace App\Models;

use App\Orientation\Dimension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'dimension', 'ordre', 'est_active'];

    protected function casts(): array
    {
        return [
            'dimension' => Dimension::class,
            'est_active' => 'boolean',
        ];
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('est_active', true);
    }
}
