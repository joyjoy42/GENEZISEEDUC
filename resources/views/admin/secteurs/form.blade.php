@extends('layouts.admin')

@section('titre', $secteur->exists ? 'Modifier un secteur' : 'Ajouter un secteur')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">
        {{ $secteur->exists ? 'Modifier le secteur' : 'Ajouter un secteur' }}
    </h1>

    <form method="POST"
          action="{{ $secteur->exists ? route('admin.secteurs.update', $secteur) : route('admin.secteurs.store') }}"
          class="mt-8 max-w-2xl space-y-6">
        @csrf
        @if ($secteur->exists)
            @method('PUT')
        @endif

        <x-champ nom="nom" label="Nom du secteur" :valeur="$secteur->nom" requis />

        <x-champ nom="slug" label="Slug" :valeur="$secteur->slug"
                 aide="L'adresse de la page. Laisse vide pour le déduire du nom." />

        <x-champ nom="description" label="Description" type="textarea" :valeur="$secteur->description"
                 aide="Une ou deux phrases qui disent ce que le secteur rassemble." />

        <div class="grid gap-6 sm:grid-cols-2">
            <x-champ nom="couleur" label="Couleur" :valeur="$secteur->couleur ?? '#1b1f3b'" requis
                     aide="Code hexadécimal, par exemple #2E7D6E.">
                <input type="color" name="couleur" id="couleur"
                       value="{{ old('couleur', $secteur->couleur ?? '#1b1f3b') }}"
                       class="h-12 w-full cursor-pointer rounded-xl border border-bord bg-carte px-2 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            </x-champ>

            <x-champ nom="ordre" label="Ordre d'affichage" type="number" :valeur="$secteur->ordre ?? 0" requis
                     aide="Le plus petit nombre s'affiche en premier." />
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit"
                    class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Enregistrer
            </button>
            <a href="{{ route('admin.secteurs.index') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                Annuler
            </a>
        </div>
    </form>

@endsection
