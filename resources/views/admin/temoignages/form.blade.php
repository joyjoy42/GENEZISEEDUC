@extends('layouts.admin')

@section('titre', $temoignage->exists ? 'Modifier une interview' : 'Ajouter une interview')

@section('contenu')

    <h1 class="font-display text-3xl font-bold text-nuit">
        {{ $temoignage->exists ? "Modifier l'interview" : 'Ajouter une interview' }}
    </h1>

    @if ($metiers->isEmpty())
        <p class="mt-8 rounded-2xl border border-rouille/30 bg-rouille/10 p-6 text-rouille">
            Il faut au moins un métier avant d&rsquo;enregistrer une interview.
            <a href="{{ route('admin.metiers.create') }}" class="font-semibold underline hover:no-underline">
                Créer un métier
            </a>
        </p>
    @else
        <form method="POST"
              action="{{ $temoignage->exists ? route('admin.temoignages.update', $temoignage) : route('admin.temoignages.store') }}"
              class="mt-8 max-w-3xl space-y-10">
            @csrf
            @if ($temoignage->exists)
                @method('PUT')
            @endif

            <section class="space-y-6">
                <h2 class="font-display text-xl font-bold text-nuit">Qui parle</h2>

                <x-champ nom="metier_id" label="Métier" requis>
                    <select name="metier_id" id="metier_id" required
                            class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        <option value="">Choisir un métier…</option>
                        @foreach ($metiers as $metier)
                            <option value="{{ $metier->id }}" @selected(old('metier_id', $temoignage->metier_id) == $metier->id)>
                                {{ $metier->nom }}
                            </option>
                        @endforeach
                    </select>
                </x-champ>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-champ nom="nom" label="Nom du professionnel" :valeur="$temoignage->nom" requis />
                    <x-champ nom="fonction" label="Fonction" :valeur="$temoignage->fonction" requis
                             aide="Par exemple : Patron de garage." />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-champ nom="entreprise" label="Entreprise ou atelier" :valeur="$temoignage->entreprise" />
                    <x-champ nom="ville" label="Ville" :valeur="$temoignage->ville ?? 'Cotonou'" requis />
                </div>
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">Ce qu&rsquo;il ou elle dit</h2>

                <x-champ nom="citation" label="Citation" type="textarea" :valeur="$temoignage->citation" requis
                         :lignes="4" aide="Ses mots à lui ou à elle, pas un résumé. Sans guillemets : la fiche les ajoute." />
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <h2 class="font-display text-xl font-bold text-nuit">La vidéo</h2>

                <x-champ nom="video_url" label="Lien de la vidéo publiée" type="url" :valeur="$temoignage->video_url"
                         aide="L'adresse du reel une fois publié sur TikTok ou Instagram." />

                <x-champ nom="plateforme" label="Plateforme">
                    <select name="plateforme" id="plateforme"
                            class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        <option value="">—</option>
                        @foreach (['tiktok' => 'TikTok', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(old('plateforme', $temoignage->plateforme) === $valeur)>
                                {{ $libelle }}
                            </option>
                        @endforeach
                    </select>
                </x-champ>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-champ nom="fichier_video" label="Fichier vidéo local" :valeur="$temoignage->fichier_video"
                             aide="Le chemin du rush, tant que la vidéo n'est pas publiée." />
                    <x-champ nom="duree_secondes" label="Durée en secondes" type="number" :valeur="$temoignage->duree_secondes" />
                </div>

                <x-champ nom="date_interview" label="Date de l'interview" type="date"
                         :valeur="$temoignage->date_interview?->format('Y-m-d')" />
            </section>

            <section class="space-y-6 border-t border-bord pt-10">
                <x-champ nom="est_publie" label="Publier cette interview" type="checkbox"
                         :valeur="old('est_publie', $temoignage->est_publie)"
                         aide="Une interview non publiée n'apparaît pas sur la fiche métier." />
            </section>

            <div class="flex flex-wrap items-center gap-4 border-t border-bord pt-10">
                <button type="submit"
                        class="rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                    Enregistrer
                </button>
                <a href="{{ route('admin.temoignages.index') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                    Annuler
                </a>
            </div>
        </form>
    @endif

@endsection
