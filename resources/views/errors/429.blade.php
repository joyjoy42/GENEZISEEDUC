@extends('errors.layout')

@section('titre', 'Trop de tentatives')

@section('message')
    <p>
        Tu as envoyé trop de demandes d’affilée. Attends une minute avant de réessayer :
        c’est une protection, pas une sanction.
    </p>
@endsection
