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
            // 500 Mo : la limite du bucket Supabase. Les types sont lus dans
            // le contenu du fichier, pas dans son extension.
            'media' => [
                'nullable', 'file', 'max:512000',
                'mimetypes:video/mp4,video/quicktime,video/x-matroska,audio/mpeg,audio/mp4,audio/x-m4a,audio/ogg,image/jpeg,image/png',
            ],
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
            'media' => 'média de l\'interview',
            'duree_secondes' => 'durée en secondes',
            'date_interview' => 'date de l\'interview',
        ];
    }
}
