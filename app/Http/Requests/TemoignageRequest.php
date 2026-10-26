<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemoignageRequest extends FormRequest
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
            'metier_id' => ['required', 'exists:metiers,id'],
            'nom' => ['required', 'string', 'max:255'],
            'fonction' => ['required', 'string', 'max:255'],
            'entreprise' => ['nullable', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:255'],
            'citation' => ['required', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'plateforme' => ['nullable', Rule::in(['tiktok', 'instagram', 'youtube'])],
            'fichier_video' => ['nullable', 'string', 'max:255'],
            'duree_secondes' => ['nullable', 'integer', 'min:1', 'max:36000'],
            'date_interview' => ['nullable', 'date'],
            'est_publie' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_publie' => $this->boolean('est_publie'),
            'plateforme' => $this->input('plateforme') ?: null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'metier_id' => 'métier',
            'nom' => 'nom du professionnel',
            'video_url' => 'lien de la vidéo',
            'fichier_video' => 'fichier vidéo',
            'duree_secondes' => 'durée en secondes',
            'date_interview' => 'date de l\'interview',
        ];
    }
}
