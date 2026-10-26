@extends('layouts.app')

@section('titre', 'Créer un compte')

@section('contenu')

    <div class="mx-auto max-w-md px-4 py-20 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit">Garder mes résultats</h1>
        <p class="mt-3 text-encre">
            Un compte sert à retrouver tes résultats de test, d&rsquo;un téléphone à
            l&rsquo;autre. Tu peux très bien t&rsquo;en passer : le lien de ton résultat
            suffit, garde-le.
        </p>
        <p class="mt-3 text-sm text-gris">
            On ne demande ni ton nom, ni ton numéro. Il faut avoir
            {{ \App\Models\Eleve::AGE_MINIMUM }} ans.
        </p>

        <form action="{{ route('eleves.inscrire') }}" method="POST" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-nuit">Adresse e-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       @class([
                           'mt-2 w-full rounded-xl border bg-carte px-4 py-3 text-encre focus:outline-2 focus:outline-offset-2 focus:outline-nuit',
                           'border-rouille' => $errors->has('email'),
                           'border-bord focus:border-nuit' => ! $errors->has('email'),
                       ])>
                @error('email')
                    <p class="mt-2 text-sm font-medium text-rouille">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="annee_naissance" class="block text-sm font-semibold text-nuit">Année de naissance</label>
                <input type="number" name="annee_naissance" id="annee_naissance"
                       value="{{ old('annee_naissance') }}" min="1940" max="{{ date('Y') }}"
                       inputmode="numeric" placeholder="2008" required
                       @class([
                           'mt-2 w-full rounded-xl border bg-carte px-4 py-3 text-encre focus:outline-2 focus:outline-offset-2 focus:outline-nuit',
                           'border-rouille' => $errors->has('annee_naissance'),
                           'border-bord focus:border-nuit' => ! $errors->has('annee_naissance'),
                       ])>
                @error('annee_naissance')
                    <p class="mt-2 text-sm font-medium text-rouille">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-nuit">Mot de passe</label>
                <input type="password" name="password" id="password"
                       required autocomplete="new-password" minlength="8"
                       @class([
                           'mt-2 w-full rounded-xl border bg-carte px-4 py-3 text-encre focus:outline-2 focus:outline-offset-2 focus:outline-nuit',
                           'border-rouille' => $errors->has('password'),
                           'border-bord focus:border-nuit' => ! $errors->has('password'),
                       ])>
                <p class="mt-1 text-sm text-gris">Huit caractères au minimum.</p>
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rouille">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-nuit">
                    Répète le mot de passe
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       required autocomplete="new-password"
                       class="mt-2 w-full rounded-xl border border-bord bg-carte px-4 py-3 text-encre focus:border-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Créer mon compte
            </button>
        </form>

        <p class="mt-6 text-sm text-gris">
            Tu as déjà un compte ?
            <a href="{{ route('eleves.connexion') }}" class="font-semibold text-nuit underline hover:no-underline">
                Se connecter
            </a>
        </p>

    </div>

@endsection
