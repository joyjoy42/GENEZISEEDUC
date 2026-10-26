<?php

namespace App\Http\Requests;

use App\Orientation\Dimension;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
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
            'libelle' => ['required', 'string', 'max:500'],
            'dimension' => ['required', Rule::enum(Dimension::class)],
            'ordre' => ['required', 'integer', 'min:0', 'max:999'],
            'est_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ordre' => $this->input('ordre', 0),
            'est_active' => $this->boolean('est_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'libelle' => 'texte de la question',
            'dimension' => 'famille d\'intérêts',
            'ordre' => 'ordre d\'affichage',
        ];
    }
}
