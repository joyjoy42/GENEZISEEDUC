@extends('layouts.app')

@section('titre', 'Tous les métiers')

@section('contenu')

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit sm:text-4xl">La bibliothèque des métiers</h1>
        <p class="mt-3 max-w-2xl text-encre">
            Le quotidien réel, la formation qui y mène, et les idées fausses qui collent au métier.
        </p>

        <form action="{{ route('metiers.index') }}" method="GET" class="mt-8">
            <div class="flex max-w-xl flex-col gap-3 sm:flex-row">
                <label for="recherche" class="sr-only">Chercher un métier</label>
                <input type="search" name="q" id="recherche" value="{{ $recherche }}"
                       placeholder="Chercher un métier…"
                       class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre placeholder:text-gris focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                <button type="submit"
                        class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                    Chercher
                </button>
            </div>

            @if ($secteurChoisi !== '')
                <input type="hidden" name="secteur" value="{{ $secteurChoisi }}">
            @endif
        </form>

        <nav aria-label="Filtrer par secteur" class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('metiers.index', array_filter(['q' => $recherche])) }}"
               @class([
                   'rounded-full border px-4 py-2 text-sm font-medium transition',
                   'border-nuit bg-nuit text-creme' => $secteurChoisi === '',
                   'border-bord bg-carte text-encre hover:border-nuit/40' => $secteurChoisi !== '',
               ])>
                Tous
            </a>

            @foreach ($secteurs as $secteur)
                <a href="{{ route('metiers.index', array_filter(['q' => $recherche, 'secteur' => $secteur->slug])) }}"
                   @class([
                       'rounded-full border px-4 py-2 text-sm font-medium transition',
                       'border-nuit bg-nuit text-creme' => $secteurChoisi === $secteur->slug,
                       'border-bord bg-carte text-encre hover:border-nuit/40' => $secteurChoisi !== $secteur->slug,
                   ])>
                    {{ $secteur->nom }}
                    <span class="text-xs opacity-70">{{ $secteur->metiers_count }}</span>
                </a>
            @endforeach
        </nav>

        @if ($metiers->isEmpty())
            @php($filtre = $recherche !== '' || $secteurChoisi !== '')

            <div class="mt-12 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center">
                <p class="font-display text-xl font-bold text-nuit">
                    {{ $filtre ? 'Aucun métier ne correspond' : 'La bibliothèque est encore vide' }}
                </p>

                <p class="mt-2 text-encre">
                    @if ($recherche !== '')
                        Rien pour « {{ $recherche }} » pour le moment.
                    @elseif ($secteurChoisi !== '')
                        Ce secteur n&rsquo;a pas encore de métier publié.
                    @else
                        Les premières fiches arrivent. Joy&rsquo;s World tourne les interviews le samedi.
                    @endif
                </p>

                @if ($filtre)
                    <a href="{{ route('metiers.index') }}" class="mt-4 inline-block text-sm font-semibold text-rouille underline hover:no-underline">
                        Voir tous les métiers
                    </a>
                @endif
            </div>
        @else
            <p class="mt-10 text-sm text-gris">
                {{ $metiers->total() }} métier{{ $metiers->total() > 1 ? 's' : '' }}
            </p>

            <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($metiers as $metier)
                    <li><x-carte-metier :$metier /></li>
                @endforeach
            </ul>

            <div class="mt-10">
                {{ $metiers->links() }}
            </div>
        @endif

    </div>

@endsection
