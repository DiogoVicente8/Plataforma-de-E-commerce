@props(['cents'])

<span {{ $attributes }}>{{ \App\Support\Money::format($cents) }}</span>