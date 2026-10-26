@extends('errors.layout')

@section('titre', 'Quelque chose a cassé de notre côté')

@section('message')
    <p>
        Ce n’est pas toi : une erreur s’est produite sur le serveur. L’équipe en est
        informée. Réessaie dans un moment.
    </p>
@endsection
