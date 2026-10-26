@extends('layouts.admin')

@section('titre', $filiere->exists ? 'Modifier une filière' : 'Ajouter une filière')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">
        {{ $filiere->exists ? 'Modifier la filière' : 'Ajouter une filière' }}
    </h1>

    <form method="POST"
          action="{{ $filiere->exists ? route('admin.filieres.update', $filiere) : route('admin.filieres.store') }}"
          class="mt-8 max-w-2xl space-y-6">
        @csrf
        @if ($filiere->exists)
            @method('PUT')
        @endif

        <x-champ nom="nom" label="Nom de la filière" :valeur="$filiere->nom" requis />

        <x-champ nom="slug" label="Slug" :valeur="$filiere->slug"
                 aide="Laisse vide pour le déduire du nom." />

        <div class="grid gap-6 sm:grid-cols-2">
            <x-champ nom="niveau" label="Niveau" :valeur="$filiere->niveau"
                     aide="Par exemple : CAP / CQP, Licence, Licence puis Master." />

            <x-champ nom="duree_annees" label="Durée en années" type="number" :valeur="$filiere->duree_annees" />
        </div>

        <x-champ nom="description" label="Description" type="textarea" :valeur="$filiere->description"
                 aide="Ce qu'on y apprend, et ce qui la distingue d'une filière voisine." />

        <x-champ nom="etablissements" label="Où se former" type="textarea" :valeur="$filiere->etablissements"
                 :lignes="3" aide="Les écoles, universités ou ateliers qui proposent cette formation." />

        <x-champ nom="formation_disponible_pays" label="Cette formation existe au Bénin" type="checkbox"
                 :valeur="$filiere->exists ? $filiere->formation_disponible_pays : true"
                 aide="Décoche si aucun établissement du pays ne la propose : la fiche le dira franchement au lieu de laisser l'élève le découvrir seul." />

        <x-champ nom="financements" label="Bourses et financements" type="textarea" :valeur="$filiere->financements"
                 :lignes="3" aide="Les bourses, concours et programmes qui peuvent la payer, avec la date de vérification." />

        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit"
                    class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Enregistrer
            </button>
            <a href="{{ route('admin.filieres.index') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                Annuler
            </a>
        </div>
    </form>

@endsection
