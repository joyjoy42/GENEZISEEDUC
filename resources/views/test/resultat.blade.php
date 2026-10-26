@extends('layouts.app')

@section('titre', 'Ton profil')
@section('description', 'Ton profil d\'intérêts et les métiers de la bibliothèque qui vont dans ce sens.')

@section('contenu')

    @php($dominantes = $profil->dominantes())

    <section class="bg-nuit text-creme">
        <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-20">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-mandarine">Ton profil</p>

            @if ($profilPlat)
                <h1 class="mt-6 font-display text-4xl font-bold leading-tight sm:text-5xl">
                    Tes réponses se ressemblent toutes.
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-creme/80">
                    Tu as répondu à peu près la même chose partout, donc rien ne ressort assez
                    pour te conseiller quoi que ce soit. Ce n&rsquo;est pas grave, et ça ne dit
                    rien de toi : refais le test en tranchant plus, même quand tu hésites.
                </p>
            @else
                <h1 class="mt-6 font-display text-4xl font-bold leading-tight sm:text-5xl">
                    @foreach ($dominantes as $dimension)
                        <span>{{ $dimension->libelle() }}</span>@if (! $loop->last)<span class="text-creme/40"> · </span>@endif
                    @endforeach
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-creme/80">
                    {{ $dominantes[0]->description() }}
                </p>
            @endif

            <p class="mt-8 text-sm text-creme/60">
                Garde l&rsquo;adresse de cette page pour y revenir : elle est à toi, et elle ne
                contient aucune donnée personnelle.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6">

        <section>
            <h2 class="font-display text-2xl font-bold text-nuit">Tes six scores</h2>
            <p class="mt-2 text-encre">Tout le monde a un peu de chaque famille. Regarde surtout l&rsquo;écart entre elles.</p>

            <ul class="mt-8 space-y-5">
                @foreach ($dimensions as $dimension)
                    @php($score = $profil->score($dimension))
                    <li>
                        <div class="flex items-baseline justify-between gap-4">
                            <span class="font-semibold text-nuit">{{ $dimension->libelle() }}</span>
                            <span class="text-sm font-semibold text-gris">{{ $score }} / 100</span>
                        </div>
                        <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-bord">
                            <div class="h-full rounded-full"
                                 style="width: {{ max($score, 1) }}%; background: {{ $dimension->couleur() }}"
                                 role="img"
                                 aria-label="{{ $dimension->libelle() }} : {{ $score }} sur 100"></div>
                        </div>
                        <p class="mt-2 text-sm text-gris">{{ $dimension->description() }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-16 border-t border-bord pt-14">
            <h2 class="font-display text-2xl font-bold text-nuit">Les métiers à aller regarder</h2>

            @if ($profilPlat)
                <p class="mt-4 rounded-2xl border border-dashed border-bord bg-carte p-8 text-center text-gris">
                    Pas de recommandation tant que tes réponses ne se départagent pas.
                    Parcours plutôt la bibliothèque librement.
                </p>
            @elseif ($metiers->isEmpty())
                <p class="mt-4 rounded-2xl border border-dashed border-bord bg-carte p-8 text-center text-gris">
                    Aucun métier de la bibliothèque n&rsquo;a encore de profil renseigné.
                    Reviens quand de nouvelles fiches seront publiées.
                </p>
            @else
                @if ($deSecours)
                    <p class="mt-4 rounded-xl border border-bord bg-sable px-5 py-4 text-encre">
                        Aucun métier de la bibliothèque ne ressort nettement pour toi. Voici
                        quand même le plus proche — à prendre comme un point de départ, pas
                        comme une réponse.
                    </p>
                @else
                    <p class="mt-2 text-encre">
                        L&rsquo;indice dit à quel point ce qui ressort chez toi ressort aussi
                        dans le métier. Ce n&rsquo;est pas une note, et ce n&rsquo;est pas un
                        classement de valeur.
                    </p>
                @endif

                <ul class="mt-8 space-y-4">
                    @foreach ($metiers as $entree)
                        @php($metier = $entree['metier'])
                        <li class="rounded-2xl border border-bord bg-carte p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex-1">
                                    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest"
                                          style="color: {{ $metier->secteur->couleur }}">
                                        <span class="inline-block size-2 rounded-full" style="background: {{ $metier->secteur->couleur }}" aria-hidden="true"></span>
                                        {{ $metier->secteur->nom }}
                                    </span>

                                    <h3 class="mt-2 font-display text-xl font-bold text-nuit">
                                        <a href="{{ route('metiers.show', $metier) }}" class="underline hover:no-underline">
                                            {{ $metier->nom }}
                                        </a>
                                    </h3>

                                    <p class="mt-2 text-sm leading-relaxed text-encre">{{ $metier->accroche }}</p>

                                    @if ($metier->temoignages_count > 0)
                                        <p class="mt-3 text-sm font-semibold text-vert">
                                            {{ $metier->temoignages_count }} interview{{ $metier->temoignages_count > 1 ? 's' : '' }} à regarder
                                        </p>
                                    @endif
                                </div>

                                <div class="shrink-0 sm:text-right">
                                    <span class="block text-sm font-semibold" style="color: {{ $entree['proximite']->couleur() }}">
                                        {{ $entree['proximite']->libelle() }}
                                    </span>
                                    <span class="mt-1 block font-display text-2xl font-bold text-nuit">
                                        {{ $entree['affinite'] }}<span class="text-base font-medium text-gris"> / 100</span>
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if ($filieres->isNotEmpty())
            <section class="mt-16 border-t border-bord pt-14">
                <h2 class="font-display text-2xl font-bold text-nuit">Les formations qui y mènent</h2>
                <p class="mt-2 text-encre">Celles qui reviennent le plus souvent dans les métiers ci-dessus.</p>

                <ul class="mt-8 space-y-4">
                    @foreach ($filieres as $entree)
                        <li class="rounded-2xl border border-bord bg-carte p-6">
                            <div class="flex flex-wrap items-baseline justify-between gap-3">
                                <h3 class="font-display text-lg font-bold text-nuit">{{ $entree['filiere']->nom }}</h3>
                                @if ($entree['filiere']->niveau)
                                    <span class="text-sm font-medium text-gris">
                                        {{ $entree['filiere']->niveau }}@if ($entree['filiere']->duree_annees) · {{ $entree['filiere']->duree_annees }} ans @endif
                                    </span>
                                @endif
                            </div>

                            @if ($entree['filiere']->description)
                                <p class="mt-3 text-sm leading-relaxed text-encre">{{ $entree['filiere']->description }}</p>
                            @endif

                            <p class="mt-3 text-sm text-gris">
                                Mène à :
                                @foreach ($entree['metiers'] as $metier)
                                    <a href="{{ route('metiers.show', $metier) }}" class="font-medium text-nuit underline hover:no-underline">{{ $metier->nom }}</a>@if (! $loop->last), @endif
                                @endforeach
                            </p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-16 rounded-2xl border border-bord bg-sable p-8">
            <h2 class="font-display text-xl font-bold text-nuit">Et maintenant ?</h2>
            <p class="mt-3 leading-relaxed text-encre">
                Un pourcentage ne remplace pas une journée passée à regarder quelqu&rsquo;un faire
                son travail. Ouvre les fiches, regarde les interviews, et vois lequel de ces
                métiers te donne envie d&rsquo;en savoir plus.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('metiers.index') }}"
                   class="rounded-xl bg-nuit px-6 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                    Parcourir tous les métiers
                </a>
                <a href="{{ route('test.questions') }}"
                   class="rounded-xl border border-bord bg-carte px-6 py-3 text-sm font-semibold text-nuit transition hover:border-nuit/40 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                    Refaire le test
                </a>
            </div>

            @guest('eleve')
                <p class="mt-6 border-t border-bord pt-6 text-sm text-encre">
                    <strong class="font-semibold text-nuit">Garde cette page.</strong>
                    Son adresse est la seule façon d&rsquo;y revenir. Mets-la en favori, ou
                    <a href="{{ route('eleves.inscription') }}" class="font-semibold text-nuit underline hover:no-underline">crée un compte</a>
                    pour la retrouver depuis n&rsquo;importe quel téléphone.
                </p>
            @endguest
        </section>

    </div>

@endsection
