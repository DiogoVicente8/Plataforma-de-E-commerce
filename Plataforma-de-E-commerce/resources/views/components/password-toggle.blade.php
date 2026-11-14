@props(['for'])

<div x-data="{ visible: false }" class="relative mt-2">
    {{ $slot }}
    <button type="button" @click="visible = !visible"
            aria-controls="{{ $for }}" :aria-pressed="visible.toString()"
            :aria-label="visible ? @js(__('Ocultar palavra-passe')) : @js(__('Mostrar palavra-passe'))"
            aria-label="{{ __('Mostrar palavra-passe') }}"
            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-gray-500 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-900">
        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
            <circle cx="12" cy="12" r="3" />
            <path x-show="visible" style="display: none" d="m3 3 18 18" />
        </svg>
    </button>
</div>
