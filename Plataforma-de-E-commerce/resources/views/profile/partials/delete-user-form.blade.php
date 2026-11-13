<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Eliminar conta') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ao eliminares a tua conta, os teus dados serão eliminados permanentemente. Guarda primeiro as informações que pretendes manter.') }}
        </p>
    </header>

    <x-danger-button
        class="!rounded-full !px-5 !py-3 !text-sm !normal-case !tracking-normal"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Eliminar conta') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Tens a certeza de que queres eliminar a tua conta?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Esta ação é permanente. Introduz a tua palavra-passe para confirmar a eliminação da conta.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Palavra-passe') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Palavra-passe') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-y-3">
                <x-secondary-button class="!rounded-full !normal-case !tracking-normal" x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-danger-button class="ms-3 !rounded-full !normal-case !tracking-normal">
                    {{ __('Eliminar conta') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
