<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Projet Supabase
    |--------------------------------------------------------------------------
    |
    | Supabase fournit trois choses à GENEZISE : la base Postgres (configurée
    | dans database.php sous la connexion « pgsql »), l'authentification des
    | comptes élèves, et le stockage des médias d'interviews.
    |
    */

    'url' => env('SUPABASE_URL'),

    /*
    | Clé publiable : elle peut apparaître dans une page, elle ne donne que ce
    | que les règles d'accès (RLS) autorisent.
    */
    'publishable_key' => env('SUPABASE_PUBLISHABLE_KEY'),

    /*
    | Clé secrète (service_role) : elle contourne toutes les règles d'accès.
    | Elle reste côté serveur, jamais dans un dépôt ni dans le navigateur.
    */
    'secret_key' => env('SUPABASE_SECRET_KEY'),

    'auth' => [
        'jwks_url' => env('SUPABASE_URL').'/auth/v1/.well-known/jwks.json',
    ],

    'storage' => [
        'bucket_interviews' => env('SUPABASE_BUCKET_INTERVIEWS', 'interviews'),
    ],

];
