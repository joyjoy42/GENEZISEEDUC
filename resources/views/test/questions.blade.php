@extends('layouts.app')

@section('titre', 'Le questionnaire')

@section('contenu')

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">

        <h1 class="font-display text-3xl font-bold text-nuit sm:text-4xl">À quel point ça te ressemble ?</h1>
        <p class="mt-3 text-encre">
            {{ $questions->count() }} phrases. Réponds vite, sans trop réfléchir : la première
            réaction est la plus juste. Il n&rsquo;y a pas de bonne réponse.
        </p>

        {{--
            Les trois réponses sont expliquées ici, une seule fois. Les répéter
            sous chaque question rendait la page deux fois plus longue sur
            téléphone, pour une précision qu'on ne lit qu'au début.
        --}}
        <dl class="mt-8 grid gap-3 rounded-2xl border border-bord bg-sable p-5 sm:grid-cols-3">
            @foreach ($reponses as $reponse)
                <div>
                    <dt class="font-display font-bold text-nuit">{{ $reponse->libelle() }}</dt>
                    <dd class="text-sm text-encre">{{ $reponse->precision() }}</dd>
                </div>
            @endforeach
        </dl>

        @error('reponses')
            <p class="mt-6 rounded-xl border border-rouille/30 bg-rouille/10 px-5 py-4 font-medium text-rouille" role="alert">
                {{ $message }}
            </p>
        @enderror

        @if ($questions->isEmpty())
            <p class="mt-8 rounded-2xl border border-dashed border-bord bg-carte p-10 text-center text-gris">
                Aucune question active pour le moment.
            </p>
        @else
            <form action="{{ route('test.calculer') }}" method="POST" class="mt-10">
                @csrf

                <ol class="space-y-4 sm:space-y-6">
                    @foreach ($questions as $index => $question)
                        <li>
                            <fieldset class="rounded-2xl border border-bord bg-carte p-4 sm:p-6">
                                <legend class="px-1 text-xs font-semibold uppercase tracking-widest text-gris">
                                    Question {{ $index + 1 }} sur {{ $questions->count() }}
                                </legend>

                                <p class="font-display text-lg font-bold leading-snug text-nuit">
                                    {{ $question->libelle }}
                                </p>

                                <div class="mt-4 grid grid-cols-3 gap-2">
                                    @foreach ($reponses as $reponse)
                                        <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border border-bord px-2 py-2 text-center text-sm font-semibold leading-tight transition hover:border-nuit/40 has-checked:border-nuit has-checked:bg-nuit has-checked:text-creme has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-nuit">
                                            <input type="radio"
                                                   name="reponses[{{ $question->id }}]"
                                                   value="{{ $reponse->value }}"
                                                   @checked(old("reponses.{$question->id}") == $reponse->value)
                                                   required
                                                   class="sr-only">
                                            {{ $reponse->libelle() }}
                                            <span class="sr-only">&nbsp;— {{ $reponse->precision() }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <button type="submit"
                            class="rounded-xl bg-nuit px-8 py-4 font-semibold text-creme transition hover:bg-nuit/90 focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
                        Voir mon profil
                    </button>
                    <a href="{{ route('test.intro') }}" class="text-sm font-semibold text-gris underline hover:no-underline">
                        Revenir aux explications
                    </a>
                </div>
            </form>
        @endif

    </div>

@endsection
