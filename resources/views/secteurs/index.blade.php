@extends('layouts.app')

@section('titre', 'Les secteurs')

@section('contenu')

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit sm:text-4xl">Les secteurs</h1>
        <p class="mt-3 max-w-2xl text-encre">
            Chaque secteur est entré dans la bibliothèque après une visite de terrain.
        </p>

        @if ($secteurs->isEmpty())
            <p class="mt-10 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
                Aucun secteur pour le moment. Les premiers arrivent avec les premières visites de terrain.
            </p>
        @else
        <ul class="mt-10 space-y-4">
            @foreach ($secteurs as $secteur)
                <li>
                    <a href="{{ route('secteurs.show', $secteur) }}"
                       class="group flex flex-col gap-3 rounded-2xl border border-bord bg-carte p-8 transition hover:shadow-sm focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        <span class="block h-1.5 w-16 rounded-full" style="background: {{ $secteur->couleur }}" aria-hidden="true"></span>

                        <div class="flex flex-wrap items-baseline justify-between gap-3">
                            <h2 class="font-display text-2xl font-bold text-nuit group-hover:underline">{{ $secteur->nom }}</h2>
                            <span class="text-sm font-medium text-gris">
                                {{ $secteur->metiers_count }} métier{{ $secteur->metiers_count > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <p class="max-w-3xl leading-relaxed text-encre">{{ $secteur->description }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
        @endif

    </div>

@endsection
