@props(['metier', 'nombreTemoignages' => null])

<a href="{{ route('metiers.show', $metier) }}"
   class="group flex h-full flex-col gap-3 rounded-2xl border border-bord bg-carte p-6 transition hover:border-nuit/30 hover:shadow-sm focus:outline-2 focus:outline-offset-2 focus:outline-nuit">

    <span class="inline-flex w-fit items-center gap-2 text-xs font-semibold uppercase tracking-widest"
          style="color: {{ $metier->secteur->couleur }}">
        <span class="inline-block size-2 rounded-full" style="background: {{ $metier->secteur->couleur }}" aria-hidden="true"></span>
        {{ $metier->secteur->nom }}
    </span>

    <h3 class="font-display text-xl font-bold text-nuit group-hover:underline">
        {{ $metier->nom }}
    </h3>

    <p class="flex-1 text-sm leading-relaxed text-encre">
        {{ $metier->accroche }}
    </p>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gris">
        @if ($metier->niveau_etude)
            <span>{{ $metier->niveau_etude }}</span>
        @endif

        @php($compte = $nombreTemoignages ?? $metier->temoignages_count ?? null)
        @if ($compte)
            <span class="font-medium text-vert">
                {{ $compte }} interview{{ $compte > 1 ? 's' : '' }}
            </span>
        @endif
    </div>
</a>
