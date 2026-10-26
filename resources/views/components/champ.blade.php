@props([
    'nom',
    'label',
    'type' => 'text',
    'valeur' => null,
    'requis' => false,
    'aide' => null,
    'lignes' => 4,
])

@php
    $enErreur = $errors->has($nom);

    // Les listes (compétences, outils) reviennent en tableau après une erreur
    // de validation : on les réaffiche une ligne par entrée.
    $contenu = old($nom, $valeur);
    $contenu = is_array($contenu) ? implode("\n", $contenu) : $contenu;

    $classes = 'w-full rounded-xl border bg-carte px-4 py-3 text-encre focus:outline-2 focus:outline-offset-2 focus:outline-nuit '
        .($enErreur ? 'border-rouille' : 'border-bord focus:border-nuit');
@endphp

<div class="flex h-full flex-col">
    @if ($type === 'checkbox')
        <label class="flex items-start gap-3">
            <input type="hidden" name="{{ $nom }}" value="0">
            <input type="checkbox" name="{{ $nom }}" id="{{ $nom }}" value="1"
                   @checked($valeur)
                   class="mt-0.5 size-4 rounded border-bord text-nuit focus:outline-2 focus:outline-offset-2 focus:outline-nuit">
            <span>
                <span class="block text-sm font-semibold text-nuit">{{ $label }}</span>
                @if ($aide)
                    <span class="mt-1 block text-sm text-gris">{{ $aide }}</span>
                @endif
            </span>
        </label>
    @else
        <label for="{{ $nom }}" class="block text-sm font-semibold text-nuit">
            {{ $label }}
            @unless ($requis)
                <span class="font-normal text-gris">(facultatif)</span>
            @endunless
        </label>

        @if ($aide)
            <p id="{{ $nom }}-aide" class="mt-1 text-sm text-gris">{{ $aide }}</p>
        @endif

        {{-- mt-auto aligne les champs voisins quand l'un d'eux a un texte d'aide --}}
        <div class="mt-2 pt-0 sm:mt-auto sm:pt-2">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @elseif ($type === 'textarea')
                <textarea name="{{ $nom }}" id="{{ $nom }}" rows="{{ $lignes }}"
                          @required($requis)
                          @if ($aide) aria-describedby="{{ $nom }}-aide" @endif
                          class="{{ $classes }}">{{ $contenu }}</textarea>
            @else
                <input type="{{ $type }}" name="{{ $nom }}" id="{{ $nom }}"
                       value="{{ $contenu }}"
                       @required($requis)
                       @if ($aide) aria-describedby="{{ $nom }}-aide" @endif
                       class="{{ $classes }}">
            @endif
        </div>
    @endif

    @error($nom)
        <p class="mt-2 text-sm font-medium text-rouille">{{ $message }}</p>
    @enderror
</div>
