<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'La bibliothèque des métiers') · GENEZISE</title>
    <meta name="description" content="@yield('description', 'Découvre les métiers de l\'intérieur : le quotidien réel, la formation, et les professionnels qui les exercent au Bénin.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-creme font-sans text-encre antialiased">

<a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-nuit focus:px-4 focus:py-2 focus:text-creme">
    Aller au contenu
</a>

<header class="border-b border-bord bg-carte">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <a href="{{ route('accueil') }}" class="flex items-baseline gap-2">
            <span class="font-display text-2xl font-bold tracking-tight text-nuit">GENEZISE</span>
            <span class="text-sm font-medium text-gris">Éducation</span>
        </a>

        <nav aria-label="Navigation principale" class="flex items-center gap-1 text-sm font-medium">
            <a href="{{ route('metiers.index') }}"
               @class([
                   'rounded-lg px-3 py-2 transition hover:bg-creme hover:text-nuit',
                   'bg-creme text-nuit' => request()->routeIs('metiers.*'),
                   'text-encre' => ! request()->routeIs('metiers.*'),
               ])>
                Les métiers
            </a>
            <a href="{{ route('secteurs.index') }}"
               @class([
                   'rounded-lg px-3 py-2 transition hover:bg-creme hover:text-nuit',
                   'bg-creme text-nuit' => request()->routeIs('secteurs.*'),
                   'text-encre' => ! request()->routeIs('secteurs.*'),
               ])>
                Les secteurs
            </a>
            <a href="{{ route('test.intro') }}"
               @class([
                   'rounded-lg px-3 py-2 transition hover:bg-creme hover:text-nuit',
                   'bg-creme text-nuit' => request()->routeIs('test.*'),
                   'text-encre' => ! request()->routeIs('test.*'),
               ])>
                Le test
            </a>

            @auth('eleve')
                <a href="{{ route('eleves.suivi') }}"
                   @class([
                       'ml-2 rounded-lg px-3 py-2 transition hover:bg-creme hover:text-nuit',
                       'bg-creme text-nuit' => request()->routeIs('eleves.*'),
                       'text-encre' => ! request()->routeIs('eleves.*'),
                   ])>
                    Mon suivi
                </a>
            @endauth

            @auth
                <a href="{{ route('admin.tableau-bord') }}"
                   class="ml-2 rounded-lg bg-nuit px-3 py-2 text-creme transition hover:bg-nuit/90">
                    Back-office
                </a>
            @endauth
        </nav>
    </div>
</header>

<main id="contenu">
    @yield('contenu')
</main>

<footer class="mt-20 border-t border-bord bg-nuit text-creme">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <div class="flex flex-col gap-8 sm:flex-row sm:justify-between">
            <div class="max-w-md">
                <p class="font-display text-xl font-bold">GENEZISE Éducation</p>
                <p class="mt-3 text-sm leading-relaxed text-creme/70">
                    La bibliothèque des métiers est nourrie par les interviews Joy&rsquo;s World,
                    tournées dans de vrais lieux de travail à Cotonou.
                </p>
            </div>
            <div class="text-sm text-creme/70">
                <p class="font-medium text-creme">Joy&rsquo;s World</p>
                <p class="mt-2">Nouvelles interviews le samedi.</p>
                <p>Publications mardi, jeudi et vendredi.</p>
            </div>
        </div>
        <p class="mt-10 border-t border-creme/15 pt-6 text-xs text-creme/50">
            Cotonou, Bénin · {{ now()->year }}
        </p>
    </div>
</footer>

</body>
</html>
