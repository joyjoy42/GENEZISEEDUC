<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Back-office') · GENEZISE</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-creme font-sans text-encre antialiased">

<a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-nuit focus:px-4 focus:py-2 focus:text-creme">
    Aller au contenu
</a>

<header class="bg-nuit text-creme">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <a href="{{ route('admin.tableau-bord') }}" class="flex items-baseline gap-2">
            <span class="font-display text-xl font-bold tracking-tight">GENEZISE</span>
            <span class="text-sm text-creme/60">Back-office</span>
        </a>

        <div class="flex items-center gap-4">
            <a href="{{ route('accueil') }}" class="text-sm text-creme/70 underline hover:text-creme hover:no-underline">
                Voir le site
            </a>
            <form action="{{ route('deconnexion') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-creme/70 underline hover:text-creme hover:no-underline">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>

    <nav aria-label="Sections du back-office" class="border-t border-creme/10">
        <div class="mx-auto flex max-w-6xl flex-wrap gap-1 px-4 py-2 text-sm font-medium sm:px-6">
            @foreach ([
                'admin.tableau-bord' => 'Tableau de bord',
                'admin.metiers.index' => 'Métiers',
                'admin.temoignages.index' => 'Interviews',
                'admin.secteurs.index' => 'Secteurs',
                'admin.filieres.index' => 'Filières',
                'admin.questions.index' => 'Questions du test',
            ] as $route => $libelle)
                @php($base = Str::beforeLast($route, '.index'))
                <a href="{{ route($route) }}"
                   @class([
                       'rounded-lg px-3 py-2 transition',
                       'bg-creme/15 text-creme' => request()->routeIs($base.'*'),
                       'text-creme/70 hover:bg-creme/10 hover:text-creme' => ! request()->routeIs($base.'*'),
                   ])>
                    {{ $libelle }}
                </a>
            @endforeach
        </div>
    </nav>
</header>

<main id="contenu" class="mx-auto max-w-6xl px-4 py-10 sm:px-6">

    @if (session('succes'))
        <p class="mb-8 rounded-xl border border-vert/30 bg-vert/10 px-5 py-4 font-medium text-vert" role="status">
            {{ session('succes') }}
        </p>
    @endif

    @if ($errors->any())
        <div class="mb-8 rounded-xl border border-rouille/30 bg-rouille/10 px-5 py-4 text-rouille" role="alert">
            <p class="font-semibold">Le formulaire n&rsquo;a pas pu être enregistré.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('contenu')

</main>

</body>
</html>
