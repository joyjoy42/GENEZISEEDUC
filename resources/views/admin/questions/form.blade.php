@extends('layouts.admin')

@section('titre', $question->exists ? 'Modifier une question' : 'Ajouter une question')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">
        {{ $question->exists ? 'Modifier la question' : 'Ajouter une question' }}
    </h1>

    <form method="POST"
          action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
          class="mt-8 max-w-2xl space-y-6">
        @csrf
        @if ($question->exists)
            @method('PUT')
        @endif

        <x-champ nom="libelle" label="Texte de la question" type="textarea" :valeur="$question->libelle" requis
                 :lignes="3"
                 aide="Décris une activité concrète, pas un trait de caractère. L'étudiant répondra de « pas du tout » à « tout à fait »." />

        <x-champ nom="dimension" label="Famille d'intérêts" requis
                 aide="La famille à laquelle cette question ajoute des points.">
            <select name="dimension" id="dimension" required
                    class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                <option value="">Choisir une famille…</option>
                @foreach ($dimensions as $dimension)
                    <option value="{{ $dimension->value }}"
                            @selected(old('dimension', $question->dimension?->value) === $dimension->value)>
                        {{ $dimension->libelle() }} — {{ $dimension->description() }}
                    </option>
                @endforeach
            </select>
        </x-champ>

        <x-champ nom="ordre" label="Ordre d'affichage" type="number" :valeur="$question->ordre ?? 0" requis
                 aide="Alterne les familles plutôt que de les enchaîner : quatre questions de suite sur le même thème faussent les réponses." />

        <x-champ nom="est_active" label="Question active" type="checkbox"
                 :valeur="old('est_active', $question->est_active ?? true)"
                 aide="Une question retirée ne s'affiche plus dans le test, mais les anciens résultats gardent le profil qu'ils avaient." />

        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit"
                    class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Enregistrer
            </button>
            <a href="{{ route('admin.questions.index') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                Annuler
            </a>
        </div>
    </form>

@endsection
