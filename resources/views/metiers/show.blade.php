@extends('layouts.app')

@section('titre', $metier->nom)
@section('description', $metier->accroche)

@section('contenu')

    <article>

        <header class="bg-nuit text-creme">
            <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-20">
                <nav aria-label="Fil d'Ariane" class="text-sm text-creme/60">
                    <a href="{{ route('metiers.index') }}" class="underline hover:no-underline">Les métiers</a>
                    <span aria-hidden="true" class="px-2">/</span>
                    <a href="{{ route('secteurs.show', $metier->secteur) }}" class="underline hover:no-underline">
                        {{ $metier->secteur->nom }}
                    </a>
                </nav>

                <h1 class="mt-6 font-display text-4xl font-bold leading-tight sm:text-5xl">{{ $metier->nom }}</h1>

                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-creme/85">{{ $metier->accroche }}</p>

                @if ($metier->niveau_etude)
                    <p class="mt-8 inline-block rounded-full bg-creme/10 px-4 py-2 text-sm font-medium">
                        {{ $metier->niveau_etude }}
                    </p>
                @endif
            </div>
        </header>

        <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6">

            @if ($metier->idee_fausse)
                <section class="rounded-2xl border border-bord bg-sable p-8">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-rouille">Ce qu&rsquo;on entend</h2>
                    <p class="mt-3 font-display text-2xl font-bold leading-snug text-nuit">{{ $metier->idee_fausse }}</p>

                    @if ($metier->realite)
                        <h3 class="mt-8 text-xs font-semibold uppercase tracking-[0.2em] text-vert">Ce qui est vrai</h3>
                        <p class="mt-3 leading-relaxed text-encre">{{ $metier->realite }}</p>
                    @endif
                </section>
            @endif

            @if ($metier->le_quotidien)
                <section class="mt-14">
                    <h2 class="font-display text-2xl font-bold text-nuit">Le quotidien</h2>
                    <p class="mt-4 leading-relaxed text-encre">{{ $metier->le_quotidien }}</p>
                </section>
            @endif

            @if ($metier->competences || $metier->outils)
                <section class="mt-14 grid gap-8 sm:grid-cols-2">
                    @if ($metier->competences)
                        <div>
                            <h2 class="font-display text-xl font-bold text-nuit">Ce qu&rsquo;il faut savoir faire</h2>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($metier->competences as $competence)
                                    <li class="rounded-full border border-bord bg-carte px-3 py-1.5 text-sm text-encre">
                                        {{ $competence }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($metier->outils)
                        <div>
                            <h2 class="font-display text-xl font-bold text-nuit">Avec quoi il travaille</h2>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($metier->outils as $outil)
                                    <li class="rounded-full border border-bord bg-carte px-3 py-1.5 text-sm text-encre">
                                        {{ $outil }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if ($metier->filieres->isNotEmpty())
                <section class="mt-14">
                    <h2 class="font-display text-2xl font-bold text-nuit">Comment on y arrive</h2>
                    <ul class="mt-4 space-y-4">
                        @foreach ($metier->filieres as $filiere)
                            <li class="rounded-2xl border border-bord bg-carte p-6">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <h3 class="font-display text-lg font-bold text-nuit">{{ $filiere->nom }}</h3>
                                    @if ($filiere->niveau)
                                        <span class="text-sm font-medium text-gris">
                                            {{ $filiere->niveau }}@if ($filiere->duree_annees) · {{ $filiere->duree_annees }} ans @endif
                                        </span>
                                    @endif
                                </div>
                                @if ($filiere->description)
                                    <p class="mt-3 text-sm leading-relaxed text-encre">{{ $filiere->description }}</p>
                                @endif
                                @if ($filiere->etablissements)
                                    <p class="mt-3 text-sm text-gris">Où se former : {{ $filiere->etablissements }}</p>
                                @endif
                                @unless ($filiere->formation_disponible_pays)
                                    <p class="mt-3 text-sm font-medium text-rouille">
                                        Aucun établissement connu au Bénin ne propose cette formation.
                                    </p>
                                @endunless
                                @if ($filiere->financements)
                                    <p class="mt-3 text-sm text-gris">Comment la financer : {{ $filiere->financements }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="mt-14 grid gap-8 sm:grid-cols-2">
                @if ($metier->ou_travailler)
                    <div>
                        <h2 class="font-display text-xl font-bold text-nuit">Où il travaille</h2>
                        <p class="mt-3 leading-relaxed text-encre">{{ $metier->ou_travailler }}</p>
                    </div>
                @endif

                @if ($metier->debouches)
                    <div>
                        <h2 class="font-display text-xl font-bold text-nuit">Vers quoi ça mène</h2>
                        <p class="mt-3 leading-relaxed text-encre">{{ $metier->debouches }}</p>
                    </div>
                @endif
            </section>

            <section class="mt-14">
                <h2 class="font-display text-xl font-bold text-nuit">Combien ça gagne</h2>
                @if ($metier->fourchetteSalaire())
                    <p class="mt-3 font-display text-2xl font-bold text-rouille">{{ $metier->fourchetteSalaire() }}</p>
                    <p class="mt-2 text-sm text-gris">Par mois, d&rsquo;après les interviews Joy&rsquo;s World.</p>
                @else
                    <p class="mt-3 leading-relaxed text-gris">
                        Pas encore de chiffre : on ne publie une fourchette que lorsque plusieurs
                        professionnels du métier nous l&rsquo;ont donnée en interview.
                    </p>
                @endif
            </section>

            <section class="mt-14 border-t border-bord pt-14">
                <h2 class="font-display text-2xl font-bold text-nuit">Ceux qui l&rsquo;exercent</h2>

                @if ($metier->temoignages->isEmpty())
                    <p class="mt-4 rounded-2xl border border-dashed border-bord bg-carte p-8 text-center text-gris">
                        Aucune interview publiée pour ce métier. Joy&rsquo;s World tourne les prochaines le samedi.
                    </p>
                @else
                    <ul class="mt-6 space-y-6">
                        @foreach ($metier->temoignages as $temoignage)
                            <li class="rounded-2xl border border-bord bg-carte p-6">
                                <blockquote class="font-display text-xl leading-snug text-nuit">
                                    &laquo;&nbsp;{{ $temoignage->citation }}&nbsp;&raquo;
                                </blockquote>

                                <div class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm">
                                    <span class="font-semibold text-nuit">{{ $temoignage->nom }}</span>
                                    <span class="text-encre">{{ $temoignage->fonction }}</span>
                                    @if ($temoignage->entreprise)
                                        <span class="text-gris">{{ $temoignage->entreprise }}</span>
                                    @endif
                                    <span class="text-gris">{{ $temoignage->ville }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                                    @if ($temoignage->date_interview)
                                        <span class="text-gris">
                                            Interview du {{ $temoignage->date_interview->translatedFormat('j F Y') }}
                                        </span>
                                    @endif

                                    @if ($temoignage->video_url)
                                        <a href="{{ $temoignage->video_url }}" target="_blank" rel="noopener noreferrer"
                                           class="font-semibold text-rouille underline hover:no-underline">
                                            Voir l&rsquo;interview @if ($temoignage->plateforme) sur {{ ucfirst($temoignage->plateforme) }} @endif
                                        </a>
                                    @elseif ($temoignage->fichier_video)
                                        <span class="text-gris">Vidéo à publier : {{ $temoignage->fichier_video }}</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($similaires->isNotEmpty())
                <section class="mt-14 border-t border-bord pt-14">
                    <h2 class="font-display text-2xl font-bold text-nuit">Dans le même secteur</h2>
                    <ul class="mt-6 grid gap-4 sm:grid-cols-3">
                        @foreach ($similaires as $similaire)
                            <li><x-carte-metier :metier="$similaire" /></li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>

    </article>

@endsection
