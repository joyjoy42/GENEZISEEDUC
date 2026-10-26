@extends('layouts.admin')

@section('titre', 'Interviews')

@section('contenu')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold text-nuit">Interviews</h1>
        <a href="{{ route('admin.temoignages.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter une interview
        </a>
    </div>

    <p class="mt-2 text-encre">Les professionnels rencontrés par Joy&rsquo;s World, rattachés à leur métier.</p>

    @if ($temoignages->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
            Aucune interview enregistrée.
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl border border-bord bg-carte">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-bord text-xs uppercase tracking-wider text-gris">
                    <tr>
                        <th scope="col" class="px-5 py-4">Professionnel</th>
                        <th scope="col" class="px-5 py-4">Métier</th>
                        <th scope="col" class="px-5 py-4">Date</th>
                        <th scope="col" class="px-5 py-4">Vidéo</th>
                        <th scope="col" class="px-5 py-4">État</th>
                        <th scope="col" class="px-5 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bord">
                    @foreach ($temoignages as $temoignage)
                        <tr>
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-nuit">{{ $temoignage->nom }}</span>
                                <span class="block text-xs text-gris">{{ $temoignage->fonction }}</span>
                            </td>
                            <td class="px-5 py-4 text-encre">{{ $temoignage->metier->nom }}</td>
                            <td class="px-5 py-4 text-encre">
                                {{ $temoignage->date_interview?->translatedFormat('j M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-encre">
                                @if ($temoignage->video_url)
                                    <a href="{{ $temoignage->video_url }}" target="_blank" rel="noopener noreferrer"
                                       class="font-semibold text-nuit underline hover:no-underline">
                                        {{ $temoignage->plateforme ? ucfirst($temoignage->plateforme) : 'Lien' }}
                                    </a>
                                @elseif ($temoignage->fichier_video)
                                    <span class="text-xs text-gris">Fichier local</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-vert/10 text-vert' => $temoignage->est_publie,
                                    'bg-sable text-rouille' => ! $temoignage->est_publie,
                                ])>
                                    {{ $temoignage->est_publie ? 'Publiée' : 'Brouillon' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.temoignages.edit', $temoignage) }}"
                                       class="font-semibold text-nuit underline hover:no-underline">Modifier</a>
                                    <form action="{{ route('admin.temoignages.destroy', $temoignage) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette interview ?')">
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
            {{ $temoignages->links() }}
        </div>
    @endif

@endsection
