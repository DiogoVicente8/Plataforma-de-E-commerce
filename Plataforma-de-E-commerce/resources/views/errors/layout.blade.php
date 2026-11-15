<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="mx-auto max-w-xl px-6 py-24 text-center">
        <p class="mb-6 text-lg">{{ config('app.name') }}</p>
        <h1 class="mb-4 text-3xl font-bold">@yield('title')</h1>
        <p class="mb-8">@yield('message')</p>
        <a href="{{ url('/') }}" class="underline">Voltar à página inicial</a>
    </main>
</body>
</html>
