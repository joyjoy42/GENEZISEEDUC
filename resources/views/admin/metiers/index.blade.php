@extends('layouts.admin')

@section('titre', 'Métiers')

@section('contenu')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold text-nuit">Métiers</h1>
        <a href="{{ route('admin.metiers.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter un métier
        </a>
    </div>

    <form action="{{ route('admin.metiers.index') }}" method="GET" class="mt-6 flex max-w-md gap-3">
        <label for="q" class="sr-only">Chercher un métier</label>
        <input type="search" name="q" id="q" value="{{ $recherche }}" placeholder="Chercher un métier…"
               class="w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre placeholder:text-gris focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
        <button type="submit"
                class="rounded-xl border border-bord bg-carte px-5 py-3 text-sm font-semibold text-nuit transition hover:border-nuit/40 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Chercher
        </button>
    </form>

    @if ($metiers->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
            Aucun métier{{ $recherche !== '' ? ' pour « '.$recherche.' »' : '' }}.
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl border border-bord bg-carte">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-bord text-xs uppercase tracking-wider text-gris">
                    <tr>
                        <th scope="col" class="px-5 py-4">Métier</th>
                        <th scope="col" class="px-5 py-4">Secteur</th>
                        <th scope="col" class="px-5 py-4">Interviews</th>
                        <th scope="col" class="px-5 py-4">État</th>
                        <th scope="col" class="px-5 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bord">
                    @foreach ($metiers as $metier)
                        <tr>
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-nuit">{{ $metier->nom }}</span>
                                <span class="block text-xs text-gris">{{ $metier->slug }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="flex items-center gap-2 text-encre">
                                    <span class="inline-block size-2.5 shrink-0 rounded-full" style="background: {{ $metier->secteur->couleur }}" aria-hidden="true"></span>
                                    {{ $metier->secteur->nom }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-encre">{{ $metier->temoignages_count }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-vert/10 text-vert' => $metier->est_publie,
                                    'bg-sable text-rouille' => ! $metier->est_publie,
                                ])>
                                    {{ $metier->est_publie ? 'Publié' : 'Brouillon' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="flex items-center justify-end gap-4">
                                    @if ($metier->est_publie)
                                        <a href="{{ route('metiers.show', $metier) }}"
                                           class="font-semibold text-gris underline hover:no-underline">Voir</a>
                                    @endif
                                    <a href="{{ route('admin.metiers.edit', $metier) }}"
                                       class="font-semibold text-nuit underline hover:no-underline">Modifier</a>
                                    <form action="{{ route('admin.metiers.destroy', $metier) }}" method="POST"
                                          onsubmit="return confirm('Supprimer le métier « {{ $metier->nom }} » et ses interviews ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rouille underline hover:no-underline">
                                            Supprimer
                                        </button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $metiers->links() }}
        </div>
    @endif

@endsection
