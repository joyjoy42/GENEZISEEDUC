<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FiliereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('filieres', 'slug')->ignore($this->route('filiere')),
            ],
            'niveau' => ['nullable', 'string', 'max:255'],
            'duree_annees' => ['nullable', 'integer', 'min:1', 'max:15'],
            'description' => ['nullable', 'string'],
            'etablissements' => ['nullable', 'string'],
            'formation_disponible_pays' => ['boolean'],
            'financements' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nom', '')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom de la filière',
            'duree_annees' => 'durée en années',
            'etablissements' => 'établissements',
            'formation_disponible_pays' => 'disponibilité de la formation au Bénin',
            'financements' => 'financements',
        ];
    }
}
