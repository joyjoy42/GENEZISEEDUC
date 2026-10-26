<?php

namespace App\Http\Requests;

use App\Orientation\Dimension;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MetierRequest extends FormRequest
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
            'secteur_id' => ['required', 'exists:secteurs,id'],
            'nom' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('metiers', 'slug')->ignore($this->route('metier')),
            ],
            'accroche' => ['required', 'string', 'max:255'],
            'le_quotidien' => ['nullable', 'string'],
            'competences' => ['nullable', 'array', 'max:20'],
            'competences.*' => ['string', 'max:255'],
            'outils' => ['nullable', 'array', 'max:20'],
            'outils.*' => ['string', 'max:255'],
            'salaire_min' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'salaire_max' => ['nullable', 'integer', 'min:0', 'max:100000000', 'gte:salaire_min'],
            'ou_travailler' => ['nullable', 'string'],
            'debouches' => ['nullable', 'string'],
            'idee_fausse' => ['nullable', 'string'],
            'realite' => ['nullable', 'string'],
            'niveau_etude' => ['nullable', 'string', 'max:255'],
            'est_publie' => ['boolean'],
            'filieres' => ['nullable', 'array'],
            'filieres.*' => ['exists:filieres,id'],
            ...$this->reglesDuProfil(),
        ];
    }

    /**
     * Les six notes RIASEC, de 0 à 100.
     *
     * @return array<string, mixed>
     */
    private function reglesDuProfil(): array
    {
        $regles = [];

        foreach (Dimension::cases() as $dimension) {
            $regles[$dimension->colonne()] = ['required', 'integer', 'min:0', 'max:100'];
        }

        return $regles;
    }

    /**
     * Le slug se déduit du nom, et les listes sont saisies une ligne par entrée.
     */
    protected function prepareForValidation(): void
    {
        $profil = [];

        foreach (Dimension::cases() as $dimension) {
            $profil[$dimension->colonne()] = (int) $this->input($dimension->colonne(), 0);
        }

        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nom', '')),
            'competences' => $this->enListe('competences'),
            'outils' => $this->enListe('outils'),
            'est_publie' => $this->boolean('est_publie'),
            ...$profil,
        ]);
    }

    /**
     * Découpe un champ texte multiligne en liste, sans lignes vides.
     *
     * @return array<int, string>|null
     */
    private function enListe(string $champ): ?array
    {
        $brut = $this->input($champ);

        if (is_array($brut)) {
            return $brut;
        }

        $lignes = collect(preg_split('/\r\n|\r|\n/', (string) $brut))
            ->map(fn (string $ligne) => trim($ligne))
            ->filter()
            ->values()
            ->all();

        return $lignes === [] ? null : $lignes;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'secteur_id' => 'secteur',
            'nom' => 'nom du métier',
            'accroche' => 'accroche',
            'le_quotidien' => 'le quotidien',
            'salaire_min' => 'salaire minimum',
            'salaire_max' => 'salaire maximum',
            'ou_travailler' => 'où il travaille',
            'debouches' => 'débouchés',
            'idee_fausse' => 'idée fausse',
            'realite' => 'réalité',
            'niveau_etude' => 'niveau d\'études',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'salaire_max.gte' => 'Le salaire maximum doit être supérieur ou égal au salaire minimum.',
        ];
    }
}
