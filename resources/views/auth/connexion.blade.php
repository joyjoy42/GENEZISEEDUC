@extends('layouts.app')

@section('titre', 'Connexion')

@section('contenu')

    <div class="mx-auto max-w-md px-4 py-20 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit">Connexion</h1>
        <p class="mt-3 text-encre">
            Réservé à l&rsquo;équipe GENEZISE. Il n&rsquo;y a pas d&rsquo;inscription :
            les comptes sont créés en ligne de commande.
        </p>

        <form action="{{ route('connexion') }}" method="POST" class="mt-8 space-y-5">
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
                <label for="password" class="block text-sm font-semibold text-nuit">Mot de passe</label>
                <input type="password" name="password" id="password"
                       required autocomplete="current-password"
                       @class([
                           'mt-2 w-full rounded-xl border bg-carte px-4 py-3 text-encre focus:outline-2 focus:outline-offset-2 focus:outline-nuit',
                           'border-rouille' => $errors->has('password'),
                           'border-bord focus:border-nuit' => ! $errors->has('password'),
                       ])>
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rouille">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-3 text-sm text-encre">
                <input type="checkbox" name="se_souvenir" value="1"
                       class="size-4 rounded border-bord text-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Rester connectée sur cet ordinateur
            </label>

            <button type="submit"
                    class="w-full rounded-xl bg-nuit px-6 py-3 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                Se connecter
            </button>
        </form>

    </div>

@endsection
