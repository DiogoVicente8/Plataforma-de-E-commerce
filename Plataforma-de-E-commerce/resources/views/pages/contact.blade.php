@extends('layouts.page')

@section('title', __('Contactos'))

@section('page')
    <p>{{ __('Tens alguma dúvida sobre uma peça, um tamanho ou uma encomenda? Fala connosco.') }}</p>

    <h2>{{ __('Email') }}</h2>
    <p><a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a></p>

    <h2>{{ __('Instagram') }}</h2>
    <p><a href="https://www.instagram.com/b_su.clothes/" target="_blank" rel="noopener">@b_su.clothes</a></p>

    <h2>{{ __('Encomendas') }}</h2>
    <p>{{ __('Se a tua dúvida for sobre uma encomenda, indica o número da encomenda, que encontras em «As minhas encomendas».') }}</p>
@endsection