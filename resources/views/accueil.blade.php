@extends('layouts.app')

@section('titre', 'Découvre les métiers de l\'intérieur')

@section('contenu')

    <section class="bg-nuit text-creme">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-mandarine">
                La bibliothèque des métiers
            </p>

            <h1 class="mt-6 max-w-3xl font-display text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">
                Tu ne peux pas choisir un métier que tu n&rsquo;as jamais vu.
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-creme/80">
                Beaucoup d&rsquo;élèves n&rsquo;en connaissent que trois : informatique, médecine, ingénierie.
                Ici, chaque métier est raconté par quelqu&rsquo;un qui l&rsquo;exerce, filmé sur son lieu de travail.
            </p>

            <form action="{{ route('metiers.index') }}" method="GET"
                  class="mt-10 flex max-w-xl flex-col gap-3 sm:flex-row">
                <label for="recherche-accueil" class="sr-only">Chercher un métier</label>
                <input type="search" name="q" id="recherche-accueil"
                       placeholder="Couturier, mécanicien, psychologue…"
                       class="w-full rounded-xl border border-creme/20 bg-creme/10 px-4 py-3 text-creme placeholder:text-creme/50 focus:border-mandarine focus:outline-2 focus:outline-offset-2 focus:outline-mandarine">
                <button type="submit"
                        class="rounded-xl bg-mandarine px-6 py-3 font-semibold text-nuit transition hover:bg-mandarine/90 focus:outline-2 focus:outline-offset-2 focus:outline-creme">
                    Chercher
                </button>
            </form>

            <dl class="mt-12 flex flex-wrap gap-x-12 gap-y-6">
                <div>
                    <dt class="text-sm text-creme/60">Métiers documentés</dt>
                    <dd class="font-display text-3xl font-bold">{{ $nombreMetiers }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-creme/60">Interviews publiées</dt>
                    <dd class="font-display text-3xl font-bold">{{ $nombreTemoignages }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-creme/60">Secteurs</dt>
                    <dd class="font-display text-3xl font-bold">{{ $secteurs->count() }}</dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="border-b border-bord bg-sable">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="max-w-xl">
                <h2 class="font-display text-2xl font-bold text-nuit">Tu ne sais pas par où commencer ?</h2>
                <p class="mt-2 leading-relaxed text-encre">
                    Réponds à quelques questions sur ce que tu aimes faire. On te montre les
                    métiers d&rsquo;ici qui vont dans ce sens, et les interviews à regarder.
                </p>
            </div>
            <a href="{{ route('test.intro') }}"
               class="shrink-0 rounded-xl bg-nuit px-6 py-4 text-center font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Faire le test
            </a>
        </div>
    </section>

    @if ($secteurs->isNotEmpty())
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <h2 class="font-display text-2xl font-bold text-nuit sm:text-3xl">Par secteur</h2>
        <p class="mt-2 max-w-2xl text-encre">
            Chaque secteur rassemble des métiers qu&rsquo;on confond souvent entre eux.
        </p>

        <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($secteurs as $secteur)
                <li>
                    <a href="{{ route('secteurs.show', $secteur) }}"
                       class="group flex h-full flex-col gap-3 rounded-2xl border border-bord bg-carte p-6 transition hover:shadow-sm focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        <span class="block h-1.5 w-12 rounded-full" style="background: {{ $secteur->couleur }}" aria-hidden="true"></span>
                        <h3 class="font-display text-lg font-bold text-nuit group-hover:underline">{{ $secteur->nom }}</h3>
                        <p class="flex-1 text-sm leading-relaxed text-encre">{{ $secteur->description }}</p>
                        <span class="text-xs font-medium text-gris">
                            {{ $secteur->metiers_count }} métier{{ $secteur->metiers_count > 1 ? 's' : '' }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
    @endif

    @if ($derniersMetiers->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-8 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="font-display text-2xl font-bold text-nuit sm:text-3xl">Derniers métiers ajoutés</h2>
                <a href="{{ route('metiers.index') }}" class="text-sm font-semibold text-rouille underline hover:no-underline">
                    Voir toute la bibliothèque
                </a>
            </div>

            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($derniersMetiers as $metier)
                    <li><x-carte-metier :$metier /></li>
                @endforeach
            </ul>
        </section>
    @endif

@endsection
