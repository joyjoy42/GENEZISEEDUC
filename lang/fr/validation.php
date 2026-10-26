<?php

/*
 * Messages de validation en français.
 *
 * Laravel ne livre que l'anglais : sans ce fichier, un formulaire incomplet
 * répond « The accroche field is required. » au milieu d'une page française.
 *
 * Seules les règles réellement utilisées par l'application sont traduites.
 * Pour toute autre règle, Laravel retombe sur APP_FALLBACK_LOCALE.
 */

return [

    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => 'Le champ :attribute n\'est pas une URL valide.',
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des tirets bas.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être une liste.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',

    'between' => [
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file' => 'Le champ :attribute doit faire entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],

    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals' => 'Le champ :attribute doit être une date égale au :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'Le champ :attribute doit se terminer par une de ces valeurs : :values.',
    'enum' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'exists' => 'La valeur choisie pour :attribute n\'existe pas.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',

    'gt' => [
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],

    'gte' => [
        'array' => 'Le champ :attribute doit contenir au moins :value éléments.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],

    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'json' => 'Le champ :attribute doit être un document JSON valide.',

    'lt' => [
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],

    'lte' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au plus :value caractères.',
    ],

    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],

    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',

    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le champ :attribute doit faire au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],

    'not_in' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',

    'password' => [
        'letters' => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed' => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols' => 'Le champ :attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Ce :attribute est apparu dans une fuite de données. Choisis-en un autre.',
    ],

    'present' => 'Le champ :attribute doit être présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_without' => 'Le champ :attribute est obligatoire quand :values est absent.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'size' => [
        'array' => 'Le champ :attribute doit contenir :size éléments.',
        'file' => 'Le champ :attribute doit faire :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit valoir :size.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par une de ces valeurs : :values.',
    'string' => 'Le champ :attribute doit être du texte.',
    'unique' => 'Cette valeur de :attribute est déjà utilisée.',
    'uploaded' => 'Le téléversement du champ :attribute a échoué.',
    'url' => 'Le champ :attribute doit être une URL valide.',

    /*
     * Les noms de champs sont déjà définis dans chaque FormRequest, via la
     * méthode attributes(). Ce tableau sert de filet pour les autres.
     */
    'attributes' => [
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'name' => 'nom',
    ],

    'custom' => [],

];
