<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') · GENEZISE</title>
    <meta name="robots" content="noindex">
    {{--
        Volontairement sans @vite ni requête en base : une page d'erreur doit
        s'afficher même quand les assets ou la base sont justement le problème.
    --}}
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f7f3ec;
            color: #3e4466;
            font-family: 'DM Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif;
            line-height: 1.6;
        }
        main { max-width: 560px; }
        .marque {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #b5481a;
            margin: 0 0 24px;
        }
        h1 {
            margin: 0 0 16px;
            font-size: 34px;
            line-height: 1.2;
            color: #1b1f3b;
        }
        p { margin: 0 0 16px; }
        a.bouton {
            display: inline-block;
            margin-top: 16px;
            padding: 14px 28px;
            border-radius: 12px;
            background: #1b1f3b;
            color: #f7f3ec;
            font-weight: 600;
            text-decoration: none;
        }
        a.bouton:hover { background: #2a3050; }
        a.bouton:focus-visible { outline: 2px solid #e8743b; outline-offset: 2px; }
    </style>
</head>
<body>
    <main>
        <p class="marque">GENEZISE</p>
        <h1>@yield('titre')</h1>
        @yield('message')
        <a class="bouton" href="{{ url('/') }}">Revenir à l&rsquo;accueil</a>
    </main>
</body>
</html>
