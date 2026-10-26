@extends('layouts.app')

@section('titre', 'Le test d\'orientation')
@section('description', 'Vingt-quatre questions pour savoir vers quels métiers te tourner, et quelles interviews aller regarder.')

@section('contenu')

    <section class="bg-nuit text-creme">
        <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-mandarine">Le test d&rsquo;orientation</p>

            <h1 class="mt-6 font-display text-4xl font-bold leading-tight sm:text-5xl">
                {{ $nombreQuestions }} questions pour savoir par où commencer à chercher.
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-creme/80">
                Le test ne te dit pas quel métier faire. Il repère ce vers quoi tu es attiré,
                et te montre les métiers de la bibliothèque qui vont dans ce sens — pour que
                tu ailles regarder les interviews et juger par toi-même.
            </p>

            <a href="{{ route('test.questions') }}"
               class="mt-10 inline-block rounded-xl bg-mandarine px-8 py-4 font-semibold text-nuit transition hover:bg-mandarine/90 focus:outline-2 focus:outline-offset-2 focus:outline-creme">
                Commencer le test
            </a>

            <p class="mt-4 text-sm text-creme/60">
                Environ 5 minutes · Sans compte · Aucune donnée personnelle enregistrée
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
        <h2 class="font-display text-2xl font-bold text-nuit">Ce que le test mesure</h2>
        <p class="mt-3 max-w-2xl text-encre">
            Six familles d&rsquo;intérêts. Tu as un peu de chacune : ce qui compte, c&rsquo;est
            lesquelles ressortent le plus fort chez toi.
        </p>

        <ul class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach ($dimensions as $dimension)
                <li class="flex gap-4 rounded-2xl border border-bord bg-carte p-6">
                    <span class="mt-1 block h-10 w-1.5 shrink-0 rounded-full"
                          style="background: {{ $dimension->couleur() }}" aria-hidden="true"></span>
                    <span>
                        <span class="block font-display text-lg font-bold text-nuit">{{ $dimension->libelle() }}</span>
                        <span class="mt-1 block text-sm leading-relaxed text-encre">{{ $dimension->description() }}</span>
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="mt-12 rounded-2xl border border-bord bg-sable p-8">
            <h2 class="font-display text-xl font-bold text-nuit">Ce que le test ne fait pas</h2>
            <ul class="mt-4 space-y-3 text-encre">
                <li class="flex gap-3">
                    <span aria-hidden="true" class="font-bold text-rouille">·</span>
                    Il ne mesure pas ton niveau, ni tes capacités, ni tes notes.
                </li>
                <li class="flex gap-3">
                    <span aria-hidden="true" class="font-bold text-rouille">·</span>
                    Il ne décide pas à ta place : un score bas ne ferme aucune porte.
                </li>
                <li class="flex gap-3">
                    <span aria-hidden="true" class="font-bold text-rouille">·</span>
                    Il ne connaît que les métiers déjà documentés ici. Il y en aura d&rsquo;autres.
                </li>
            </ul>
        </div>
    </section>

@endsection
