@extends('layouts.admin')

@section('titre', 'Secteurs')

@section('contenu')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold text-nuit">Secteurs</h1>
        <a href="{{ route('admin.secteurs.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter un secteur
        </a>
    </div>

    @if ($secteurs->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
            Aucun secteur. Commence par en créer un : chaque métier doit appartenir à un secteur.
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl border border-bord bg-carte">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-bord text-xs uppercase tracking-wider text-gris">
                    <tr>
                        <th scope="col" class="px-5 py-4">Secteur</th>
                        <th scope="col" class="px-5 py-4">Métiers</th>
                        <th scope="col" class="px-5 py-4">Ordre</th>
                        <th scope="col" class="px-5 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bord">
                    @foreach ($secteurs as $secteur)
                        <tr>
                            <td class="px-5 py-4">
                                <span class="flex items-center gap-3">
                                    <span class="inline-block size-3 shrink-0 rounded-full" style="background: {{ $secteur->couleur }}" aria-hidden="true"></span>
                                    <span>
                                        <span class="block font-semibold text-nuit">{{ $secteur->nom }}</span>
                                        <span class="block text-xs text-gris">{{ $secteur->slug }}</span>
                                    </span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-encre">{{ $secteur->metiers_count }}</td>
                            <td class="px-5 py-4 text-encre">{{ $secteur->ordre }}</td>
                            <td class="px-5 py-4">
                                <span class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.secteurs.edit', $secteur) }}"
                                       class="font-semibold text-nuit underline hover:no-underline">Modifier</a>
                                    <form action="{{ route('admin.secteurs.destroy', $secteur) }}" method="POST"
                                          onsubmit="return confirm('Supprimer le secteur « {{ $secteur->nom }} » ?')">
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
    @endif

@endsection
