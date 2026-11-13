@extends('layouts.page')

@section('title', __('Termos e condições'))

@section('page')
    <p>{{ __('Ao comprar na :shop, aceitas estes termos.', ['shop' => config('app.name')]) }}</p>

    <h2>{{ __('Encomendas') }}</h2>
    <p>{{ __('A encomenda só fica confirmada depois do pagamento. Se uma peça esgotar entretanto, avisamos-te e devolvemos o valor pago.') }}</p>

    <h2>{{ __('Preços') }}</h2>
    <p>{{ __('Os preços são apresentados em euros e incluem IVA à taxa em vigor.') }}</p>

    <h2>{{ __('Entregas') }}</h2>
    <p>{{ __('Enviamos para moradas em Portugal. O prazo de entrega é indicado no momento da compra.') }}</p>

    <h2>{{ __('Devoluções') }}</h2>
    <p>As condições de devolução estão na página de <a href="{{ route('pages.returns') }}">trocas e devoluções</a>.</p>
@endsection