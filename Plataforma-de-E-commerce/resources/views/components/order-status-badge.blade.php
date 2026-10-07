@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\OrderStatus::Pending => 'bg-yellow-100 text-yellow-800',
        \App\Enums\OrderStatus::Paid => 'bg-blue-100 text-blue-800',
        \App\Enums\OrderStatus::Shipped => 'bg-green-100 text-green-800',
        \App\Enums\OrderStatus::Cancelled => 'bg-red-100 text-red-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}>
    {{ __($status->label()) }}
</span>
