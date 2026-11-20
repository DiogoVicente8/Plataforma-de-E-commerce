<div x-data="customerNotifications(@js(route('account.notifications.index', absolute: false)), @js(route('account.notifications.read-all', absolute: false)))"
     class="relative" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="toggle()" :aria-expanded="open" aria-controls="customer-notifications"
            :aria-label="'Notificações (' + count + ' por ler)'"
            class="relative inline-flex p-1 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-900 focus-visible:ring-offset-2">
        <svg aria-hidden="true" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
        </svg>
        <span x-show="count > 0" x-cloak x-text="count > 99 ? '99+' : count" aria-hidden="true"
              class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-gray-900 px-1 text-xs font-semibold text-white"></span>
    </button>

    <section id="customer-notifications" x-show="open" x-cloak x-transition.opacity aria-label="Notificações"
             class="fixed left-4 right-4 top-24 z-50 overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-xl sm:absolute sm:left-auto sm:right-0 sm:top-11 sm:w-96">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold">Notificações</h2>
                <p class="mt-1 text-sm text-gray-500" x-text="count === 1 ? 'Tens 1 notificação por ler.' : 'Tens ' + count + ' notificações por ler.'"></p>
            </div>
            <button type="button" @click="open = false; $el.closest('[x-data]').querySelector('button').focus()" aria-label="Fechar notificações" class="rounded p-1 text-gray-500 hover:bg-gray-100">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </div>
        <div x-show="error" role="alert" class="border-b border-gray-100 px-5 py-3 text-sm text-red-700">
            <span x-text="error"></span>
            <button type="button" @click="refresh()" class="ml-1 underline">Tentar novamente</button>
        </div>
        <p x-show="loading && notifications.length === 0" class="px-5 py-8 text-sm text-gray-500">A carregar notificações…</p>
        <div x-show="!loading && !error && notifications.length === 0" class="px-5 py-10 text-center">
            <p class="font-medium">Está tudo em dia.</p>
            <p class="mt-2 text-sm text-gray-500">As novidades e atualizações das tuas encomendas aparecem aqui.</p>
        </div>
        <ul class="max-h-[55vh] divide-y divide-gray-100 overflow-y-auto">
            <template x-for="notification in notifications" :key="notification.id">
                <li>
                    <a :href="notification.url" @click.prevent="read(notification)" class="flex gap-3 px-5 py-4 hover:bg-gray-50 focus:bg-gray-50" :class="{ 'bg-stone-50': !notification.read }">
                        <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path x-show="notification.kind === 'order'" d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10" />
                                <path x-show="notification.kind === 'collection'" stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5L12 3Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold" x-text="notification.title"></span>
                            <span class="mt-1 block text-sm leading-relaxed text-gray-600" x-text="notification.body"></span>
                            <span class="mt-2 block text-xs text-gray-500" x-text="notification.created_at"></span>
                        </span>
                        <span x-show="!notification.read" aria-label="Por ler" class="mt-2 h-2 w-2 shrink-0 rounded-full bg-gray-900"></span>
                    </a>
                </li>
            </template>
        </ul>
        <div x-show="count > 0" class="border-t border-gray-100 px-5 py-3">
            <button type="button" @click="readAll()" :disabled="busy" class="text-sm font-medium underline disabled:opacity-50">Marcar todas como lidas</button>
        </div>
    </section>

    <div x-show="toast && !open" x-cloak x-transition.opacity role="status" aria-live="polite"
         class="fixed bottom-5 left-4 right-4 z-40 flex items-center gap-3 rounded-xl bg-gray-900 px-5 py-4 text-white shadow-lg sm:left-auto sm:right-6 sm:w-80">
        <button type="button" @click="toggle()" class="flex-1 text-left text-sm" x-text="count === 1 ? 'Tens 1 notificação por ler.' : 'Tens ' + count + ' notificações por ler.'"></button>
        <button type="button" @click="toast = false" aria-label="Fechar aviso" class="text-xl leading-none">×</button>
    </div>
</div>
