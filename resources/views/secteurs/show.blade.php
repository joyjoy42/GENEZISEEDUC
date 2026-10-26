@extends('layouts.app')

@section('titre', $secteur->nom)
@section('description', $secteur->description)

@section('contenu')

    <header class="bg-nuit text-creme">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
            <nav aria-label="Fil d'Ariane" class="text-sm text-creme/60">
                <a href="{{ route('secteurs.index') }}" class="underline hover:no-underline">Les secteurs</a>
            </nav>

            <span class="mt-6 block h-1.5 w-16 rounded-full" style="background: {{ $secteur->couleur }}" aria-hidden="true"></span>

            <h1 class="mt-5 font-display text-4xl font-bold sm:text-5xl">{{ $secteur->nom }}</h1>

            @if ($secteur->description)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-creme/85">{{ $secteur->description }}</p>
            @endif
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        @if ($secteur->metiers->isEmpty())
            <p class="rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
                Aucun métier publié dans ce secteur pour le moment.
            </p>
        @else
            <h2 class="font-display text-2xl font-bold text-nuit">
                {{ $secteur->metiers->count() }} métier{{ $secteur->metiers->count() > 1 ? 's' : '' }}
            </h2>

            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($secteur->metiers as $metier)
                    <li><x-carte-metier :$metier /></li>
                @endforeach
            </ul>
        @endif
    </div>

@endsection
