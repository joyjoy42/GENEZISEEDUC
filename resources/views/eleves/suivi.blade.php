@extends('layouts.app')

@section('titre', 'Mon suivi')

@section('contenu')

    <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold text-nuit">Mon suivi</h1>
                <p class="mt-2 text-gris">{{ auth('eleve')->user()->email }}</p>
            </div>

            <form action="{{ route('eleves.deconnexion') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-semibold text-gris underline hover:no-underline">
                    Se déconnecter
                </button>
            </form>
        </div>

        @if ($resultats->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-bord bg-carte p-8 text-center">
                <p class="text-encre">Tu n&rsquo;as pas encore passé le test.</p>
                <a href="{{ route('test.intro') }}"
                   class="mt-4 inline-block rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90">
                    Passer le test
                </a>
            </div>
        @else
            <ul class="mt-10 space-y-4">
                @foreach ($resultats as $resultat)
                    <li class="rounded-2xl border border-bord bg-carte p-6">
                        <div class="flex flex-wrap items-baseline justify-between gap-3">
                            <span class="font-display text-lg font-bold text-nuit">
                                Test du {{ $resultat->created_at->translatedFormat('j F Y') }}
                            </span>
                            <span class="text-sm text-gris">{{ $resultat->nombre_reponses }} réponses</span>
                        </div>
                        <a href="{{ route('test.resultat', $resultat) }}"
                           class="mt-3 inline-block font-semibold text-rouille underline hover:no-underline">
                            Revoir ce résultat
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="mt-8 text-sm text-gris">
                Refaire le test plus tard a du sens : ce que tu aimes à 15 ans n&rsquo;est pas
                toujours ce que tu aimes à 18.
            </p>
        @endif

        <p class="mt-12 border-t border-bord pt-6 text-sm text-gris">
            <a href="{{ route('eleves.suppression') }}" class="underline hover:no-underline">
                Supprimer mon compte
            </a>
        </p>

    </div>

@endsection
