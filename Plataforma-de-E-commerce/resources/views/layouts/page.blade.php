{{-- Layout das páginas de texto (contactos e políticas). --}}
@extends('layouts.shop')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12 text-gray-700 sm:px-6 lg:px-8 [&_a]:underline [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-gray-900 [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:ps-5">
        <h1 class="text-3xl font-semibold text-gray-900">@yield('title')</h1>
        @yield('page')
    </article>
@endsection