@extends('layouts.admin')

@section('titre', 'Tableau de bord')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">Tableau de bord</h1>
    <p class="mt-2 text-encre">L&rsquo;état de la bibliothèque, et ce qui reste à remplir.</p>

    <dl class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Métiers publiés', $nombreMetiersPublies, $nombreMetiers.' au total'],
            ['Interviews publiées', $nombreTemoignagesPublies, $nombreTemoignages.' au total'],
            ['Secteurs', $nombreSecteurs, $nombreFilieres.' filière'.($nombreFilieres > 1 ? 's' : '')],
            ['Tests passés', $nombreTests, $nombreQuestions.' questions actives'],
        ] as [$libelle, $valeur, $detail])
            <div class="rounded-2xl border border-bord bg-carte p-6">
                <dt class="text-sm text-gris">{{ $libelle }}</dt>
                <dd class="mt-1 font-display text-3xl font-bold text-nuit">{{ $valeur }}</dd>
                @if ($detail)
                    <p class="mt-1 text-xs text-gris">{{ $detail }}</p>
                @endif
            </div>
        @endforeach
    </dl>

    <div class="mt-10 flex flex-wrap gap-3">
        <a href="{{ route('admin.metiers.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter un métier
        </a>
        <a href="{{ route('admin.temoignages.create') }}"
           class="rounded-xl border border-bord bg-carte px-5 py-3 text-sm font-semibold text-nuit transition hover:border-nuit/40 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter une interview
        </a>
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-2">

        <section>
            <h2 class="font-display text-xl font-bold text-nuit">Métiers publiés sans aucune interview</h2>
            <p class="mt-1 text-sm text-gris">Ce sont les fiches qui reposent encore sur du texte seul.</p>

            @if ($metiersSansInterview->isEmpty())
                <p class="mt-4 rounded-2xl border border-bord bg-carte p-6 text-sm text-gris">
                    {{ $nombreMetiersPublies === 0
                        ? 'Aucun métier publié pour l’instant.'
                        : 'Chaque métier publié a au moins une interview.' }}
                </p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($metiersSansInterview as $metier)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-bord bg-carte px-5 py-3">
                            <span class="font-medium text-nuit">{{ $metier->nom }}</span>
                            <a href="{{ route('admin.temoignages.create', ['metier' => $metier->id]) }}"
                               class="text-sm font-semibold text-rouille underline hover:no-underline">
                                Ajouter son interview
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-nuit">Métiers invisibles pour le test</h2>
            <p class="mt-1 text-sm text-gris">Leur profil d&rsquo;intérêts est resté à zéro : le test ne les recommandera à personne.</p>

            @if ($metiersSansProfil->isEmpty())
                <p class="mt-4 rounded-2xl border border-bord bg-carte p-6 text-sm text-gris">
                    {{ $nombreMetiersPublies === 0
                        ? 'Aucun métier publié pour l’instant.'
                        : 'Chaque métier publié a un profil renseigné.' }}
                </p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($metiersSansProfil as $metier)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-bord bg-carte px-5 py-3">
                            <span class="font-medium text-nuit">{{ $metier->nom }}</span>
                            <a href="{{ route('admin.metiers.edit', $metier) }}"
                               class="text-sm font-semibold text-rouille underline hover:no-underline">
                                Renseigner son profil
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-nuit">Derniers métiers modifiés</h2>

            @if ($metiersRecents->isEmpty())
                <p class="mt-4 rounded-2xl border border-bord bg-carte p-6 text-sm text-gris">
                    La bibliothèque est encore vide.
                </p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($metiersRecents as $metier)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-bord bg-carte px-5 py-3">
                            <span>
                                <a href="{{ route('admin.metiers.edit', $metier) }}" class="font-medium text-nuit underline hover:no-underline">
                                    {{ $metier->nom }}
                                </a>
                                <span class="ml-2 text-xs text-gris">{{ $metier->secteur->nom }}</span>
                            </span>
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                'bg-vert/10 text-vert' => $metier->est_publie,
                                'bg-sable text-rouille' => ! $metier->est_publie,
                            ])>
                                {{ $metier->est_publie ? 'Publié' : 'Brouillon' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

    </div>

@endsection
