@extends('layouts.page')

@section('title', __('Trocas e devoluções'))

@section('page')
    <p>{{ __('Queremos que gostes de tudo o que compras. Se uma peça não servir ou não for o que esperavas, podes devolvê-la.') }}</p>

    <h2>{{ __('Prazo') }}</h2>
    <p>{{ __('Tens 14 dias a contar da data em que recebes a encomenda para a devolver, sem teres de indicar o motivo.') }}</p>

    <h2>{{ __('Condições') }}</h2>
    <ul>
        <li>{{ __('A peça tem de estar em bom estado, sem uso e com as etiquetas.') }}</li>
        <li>{{ __('Os portes da devolução ficam a cargo do cliente, exceto quando a peça tem defeito.') }}</li>
    </ul>

    <h2>{{ __('Como devolver') }}</h2>
    <p>Contacta-nos através da página de <a href="{{ route('pages.contact') }}">contactos</a> com o número da encomenda. Depois de recebermos a peça, o reembolso é feito no prazo de 14 dias, pelo mesmo meio de pagamento.</p>
@endsection