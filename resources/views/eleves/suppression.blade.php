@extends('layouts.app')

@section('titre', 'Supprimer mon compte')

@section('contenu')

    <div class="mx-auto max-w-md px-4 py-20 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit">Supprimer mon compte</h1>

        @error('suppression')
            <p class="mt-6 rounded-xl border border-rouille/30 bg-rouille/10 p-4 text-sm font-medium text-rouille">
                {{ $message }}
            </p>
        @enderror

        <div class="mt-6 space-y-4 text-encre">
            <p>
                Ton adresse e-mail, ton année de naissance et ton mot de passe sont
                effacés. C&rsquo;est définitif, on ne peut pas revenir en arrière.
            </p>
            <p>
                <strong class="font-semibold text-nuit">Tes résultats de test ne sont pas effacés</strong>,
                ils redeviennent anonymes : plus rien ne les relie à toi. Leurs liens
                continuent de fonctionner si tu les as gardés quelque part.
            </p>
            <p class="text-sm text-gris">
                Tu pourras recréer un compte plus tard avec la même adresse, mais tes
                anciens résultats n&rsquo;y seront plus rattachés.
            </p>
        </div>

        <form action="{{ route('eleves.supprimer') }}" method="POST" class="mt-8 space-y-4">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="w-full rounded-xl bg-rouille px-6 py-3 font-semibold text-creme transition hover:bg-rouille/90 focus:outline-2 focus:outline-offset-2 focus:outline-rouille">
                Supprimer définitivement mon compte
            </button>

            <a href="{{ route('eleves.suivi') }}"
               class="block rounded-xl border border-bord bg-carte px-6 py-3 text-center font-semibold text-nuit transition hover:border-nuit/40">
                Annuler
            </a>
        </form>

    </div>

@endsection
