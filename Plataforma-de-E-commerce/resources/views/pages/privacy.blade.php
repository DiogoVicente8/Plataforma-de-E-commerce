@extends('layouts.page')

@section('title', __('Política de privacidade'))

@section('page')
    <p>{{ __('A :shop trata os teus dados pessoais de acordo com o Regulamento Geral sobre a Proteção de Dados (RGPD).', ['shop' => config('app.name')]) }}</p>

    <h2>{{ __('Que dados guardamos') }}</h2>
    <ul>
        <li>{{ __('Nome, email e palavra-passe (guardada cifrada) da tua conta.') }}</li>
        <li>{{ __('As moradas e os telefones que guardas para as entregas.') }}</li>
        <li>{{ __('As encomendas que fazes e as peças que guardas nos favoritos.') }}</li>
    </ul>

    <h2>{{ __('Para que os usamos') }}</h2>
    <p>{{ __('Apenas para gerir a tua conta, processar e entregar as tuas encomendas e responder aos teus pedidos. Não vendemos nem cedemos os teus dados a terceiros.') }}</p>

    <h2>{{ __('Os teus direitos') }}</h2>
    <p>{{ __('Podes consultar e corrigir os teus dados no teu perfil e apagar a tua conta a qualquer momento. Ao apagar a conta, as tuas moradas e favoritos são apagados também; as encomendas ficam guardadas sem ligação à conta, por obrigação fiscal.') }}</p>
    <p>Para qualquer outro pedido sobre os teus dados, usa a página de <a href="{{ route('pages.contact') }}">contactos</a>.</p>

    <h2>{{ __('Cookies') }}</h2>
    <p>{{ __('Usamos apenas os cookies necessários para manter a tua sessão iniciada e o teu carrinho.') }}</p>
@endsection