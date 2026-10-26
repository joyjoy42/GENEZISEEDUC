@extends('layouts.admin')

@section('titre', $metier->exists ? 'Modifier un métier' : 'Ajouter un métier')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">
        {{ $metier->exists ? 'Modifier le métier' : 'Ajouter un métier' }}
    </h1>

    @if ($secteurs->isEmpty())
        <p class="mt-8 rounded-2xl border border-rouille/30 bg-rouille/10 p-6 text-rouille">
            Il faut au moins un secteur avant de créer un métier.
            <a href="{{ route('admin.secteurs.create') }}" class="font-semibold underline hover:no-underline">
                Créer un secteur
            </a>
        </p>
    @else
        <form method="POST"
              action="{{ $metier->exists ? route('admin.metiers.update', $metier) : route('admin.metiers.store') }}"
              class="mt-8 max-w-3xl space-y-10">
            @csrf
            @if ($metier->exists)
                @method('PUT')
            @endif

            <section class="space-y-6">
                <h2 class="font-display text-xl font-bold text-nuit">L&rsquo;essentiel</h2>

                <x-champ nom="secteur_id" label="Secteur" requis>
                    <select name="secteur_id" id="secteur_id" required
                            class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        <option value="">Choisir un secteur…</option>
                        @foreach ($secteurs as $secteur)
                            <option value="{{ $secteur->id }}" @selected(old('secteur_id', $metier->secteur_id) == $secteur->id)>
                                {{ $secteur->nom }}
                            </option>
                        @endforeach
                    </select>
                </x-champ>

                <x-champ nom="nom" label="Nom du métier" :valeur="$metier->nom" requis />

                <x-champ nom="slug" label="Slug" :valeur="$metier->slug"
                         aide="L'adresse de la fiche. Laisse vide pour le déduire du nom." />

                <x-champ nom="accroche" label="Accroche" :valeur="$metier->accroche" requis
                         aide="Une phrase qui donne envie de lire la fiche. 255 caractères maximum." />

                <x-champ nom="niveau_etude" label="Niveau d'études" :valeur="$metier->niveau_etude"
                         aide="Par exemple : Apprentissage, CAP ou CQP." />
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Ce que l&rsquo;interview a appris</h2>

                <x-champ nom="le_quotidien" label="Le quotidien" type="textarea" :valeur="$metier->le_quotidien"
                         :lignes="6" aide="À quoi ressemble vraiment une journée. C'est le cœur de la fiche." />

                <x-champ nom="idee_fausse" label="L'idée fausse" type="textarea" :valeur="$metier->idee_fausse"
                         :lignes="2" aide="Ce qu'on entend dire sur ce métier, entre guillemets." />

                <x-champ nom="realite" label="La réalité" type="textarea" :valeur="$metier->realite"
                         :lignes="4" aide="Ce qui est vrai, et ce que l'interview a montré." />
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Compétences et outils</h2>

                <x-champ nom="competences" label="Compétences" type="textarea" :valeur="$metier->competences"
                         :lignes="5" aide="Une compétence par ligne." />

                <x-champ nom="outils" label="Outils" type="textarea" :valeur="$metier->outils"
                         :lignes="5" aide="Un outil par ligne." />
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Débouchés et salaire</h2>

                <x-champ nom="ou_travailler" label="Où il travaille" type="textarea" :valeur="$metier->ou_travailler" :lignes="3" />

                <x-champ nom="debouches" label="Vers quoi ça mène" type="textarea" :valeur="$metier->debouches" :lignes="3" />

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-champ nom="salaire_min" label="Salaire mensuel minimum (FCFA)" type="number" :valeur="$metier->salaire_min" />
                    <x-champ nom="salaire_max" label="Salaire mensuel maximum (FCFA)" type="number" :valeur="$metier->salaire_max" />
                </div>

                <p class="rounded-xl border border-bord bg-sable px-5 py-4 text-sm text-encre">
                    Laisse les deux champs vides tant que plusieurs professionnels ne t&rsquo;ont pas donné
                    leur fourchette : la fiche le dira honnêtement plutôt que d&rsquo;afficher un chiffre inventé.
                </p>
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Profil d&rsquo;intérêts (RIASEC)</h2>
                <p class="text-sm text-encre">
                    Ces six notes décident à qui le test recommande ce métier. Mets 0 à 100 selon
                    la part que chaque famille d&rsquo;intérêts occupe vraiment dans le métier.
                    Un métier laissé entièrement à 0 ne sera jamais recommandé.
                </p>

                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($dimensions as $dimension)
                        <x-champ :nom="$dimension->colonne()" :label="$dimension->libelle()" type="number"
                                 :valeur="$metier->{$dimension->colonne()} ?? 0" requis
                                 :aide="$dimension->description()" />
                    @endforeach
                </div>
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Formations qui y mènent</h2>

                @if ($filieres->isEmpty())
                    <p class="text-sm text-gris">
                        Aucune filière enregistrée.
                        <a href="{{ route('admin.filieres.create') }}" class="font-semibold text-rouille underline hover:no-underline">
                            En créer une
                        </a>
                    </p>
                @else
                    @php($choisies = collect(old('filieres', $filieresChoisies))->map(fn ($id) => (int) $id))
                    <fieldset>
                        <legend class="sr-only">Filières</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($filieres as $filiere)
                                <label class="flex items-start gap-3 rounded-xl border border-bord bg-carte px-4 py-3">
                                    <input type="checkbox" name="filieres[]" value="{{ $filiere->id }}"
                                           @checked($choisies->contains($filiere->id))
                                           class="mt-0.5 size-4 rounded border-bord text-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                                    <span>
                                        <span class="block text-sm font-medium text-nuit">{{ $filiere->nom }}</span>
                                        @if ($filiere->niveau)
                                            <span class="block text-xs text-gris">{{ $filiere->niveau }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <x-champ nom="est_publie" label="Publier cette fiche" type="checkbox"
                         :valeur="old('est_publie', $metier->est_publie)"
                         aide="Une fiche non publiée reste invisible sur le site." />
            </section>

            <div class="flex flex-wrap items-center gap-4 border-t border-bord pt-10">
                <button type="submit"
                        class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                    Enregistrer
                </button>
                <a href="{{ route('admin.metiers.index') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                    Annuler
                </a>
            </div>
        </form>
    @endif

@endsection
