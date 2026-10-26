@extends('layouts.admin')

@section('titre', 'Questions du test')

@section('contenu')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold text-nuit">Questions du test</h1>
        <a href="{{ route('admin.questions.create') }}"
           class="rounded-xl bg-nuit px-5 py-3 text-sm font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            Ajouter une question
        </a>
    </div>

    <p class="mt-2 text-encre">
        Chaque question compte pour une seule famille d&rsquo;intérêts. Garde un nombre proche
        de questions par famille, sinon celles qui en ont le plus pèsent trop lourd.
    </p>

    <ul class="mt-8 flex flex-wrap gap-3">
        @foreach ($dimensions as $dimension)
            @php($compte = $parDimension[$dimension->value] ?? 0)
            <li class="flex items-center gap-2 rounded-full border border-bord bg-carte px-4 py-2 text-sm">
                <span class="inline-block size-2.5 rounded-full" style="background: {{ $dimension->couleur() }}" aria-hidden="true"></span>
                <span class="font-medium text-nuit">{{ $dimension->libelle() }}</span>
                <span @class(['font-semibold', 'text-rouille' => $compte === 0, 'text-gris' => $compte > 0])>
                    {{ $compte }}
                </span>
            </li>
        @endforeach
    </ul>

    @if ($questions->isEmpty())
        <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
            Aucune question. Le test ne peut pas fonctionner sans questions.
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl border border-bord bg-carte">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-bord text-xs uppercase tracking-wider text-gris">
                    <tr>
                        <th scope="col" class="px-5 py-4">Ordre</th>
                        <th scope="col" class="px-5 py-4">Question</th>
                        <th scope="col" class="px-5 py-4">Famille</th>
                        <th scope="col" class="px-5 py-4">État</th>
                        <th scope="col" class="px-5 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bord">
                    @foreach ($questions as $question)
                        <tr>
                            <td class="px-5 py-4 text-gris">{{ $question->ordre }}</td>
                            <td class="px-5 py-4 font-medium text-nuit">{{ $question->libelle }}</td>
                            <td class="px-5 py-4">
                                <span class="flex items-center gap-2 text-encre">
                                    <span class="inline-block size-2.5 shrink-0 rounded-full"
                                          style="background: {{ $question->dimension->couleur() }}" aria-hidden="true"></span>
                                    {{ $question->dimension->libelle() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-vert/10 text-vert' => $question->est_active,
                                    'bg-sable text-rouille' => ! $question->est_active,
                                ])>
                                    {{ $question->est_active ? 'Active' : 'Retirée' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.questions.edit', $question) }}"
                                       class="font-semibold text-nuit underline hover:no-underline">Modifier</a>
                                    <form action="{{ route('admin.questions.destroy', $question) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette question ?')">
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
