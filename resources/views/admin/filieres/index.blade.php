@extends('layouts.admin')

@section('titre', 'Filières')

@section('contenu')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold text-nuit">Filières</h1>
        <a href="{{ route('admin.filieres.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter une filière
        </a>
    </div>

    <p class="mt-2 text-encre">Les formations qui mènent aux métiers. Une filière peut mener à plusieurs métiers.</p>

    @if ($filieres->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
            Aucune filière enregistrée.
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl border border-bord bg-carte">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-bord text-xs uppercase tracking-wider text-gris">
                    <tr>
                        <th scope="col" class="px-5 py-4">Filière</th>
                        <th scope="col" class="px-5 py-4">Niveau</th>
                        <th scope="col" class="px-5 py-4">Durée</th>
                        <th scope="col" class="px-5 py-4">Métiers</th>
                        <th scope="col" class="px-5 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bord">
                    @foreach ($filieres as $filiere)
                        <tr>
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-nuit">{{ $filiere->nom }}</span>
                                <span class="block text-xs text-gris">{{ $filiere->slug }}</span>
                            </td>
                            <td class="px-5 py-4 text-encre">{{ $filiere->niveau ?? '—' }}</td>
                            <td class="px-5 py-4 text-encre">
                                {{ $filiere->duree_annees ? $filiere->duree_annees.' ans' : '—' }}
                            </td>
                            <td class="px-5 py-4 text-encre">{{ $filiere->metiers_count }}</td>
                            <td class="px-5 py-4">
                                <span class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.filieres.edit', $filiere) }}"
                                       class="font-semibold text-nuit underline hover:no-underline">Modifier</a>
                                    <form action="{{ route('admin.filieres.destroy', $filiere) }}" method="POST"
                                          onsubmit="return confirm('Supprimer la filière « {{ $filiere->nom }} » ?')">
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
