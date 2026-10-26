<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SecteurRequest extends FormRequest
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
                Rule::unique('secteurs', 'slug')->ignore($this->route('secteur')),
            ],
            'description' => ['nullable', 'string'],
            'couleur' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'ordre' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * Le slug se déduit du nom quand on ne le saisit pas.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nom', '')),
            'ordre' => $this->input('ordre', 0),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom du secteur',
            'couleur' => 'couleur',
            'ordre' => 'ordre d\'affichage',
        ];
    }
}
