<section class="rounded-xl border border-gray-200 p-5">
    <h3 class="text-lg font-medium text-gray-900">{{ __('Imagens') }}</h3>
    <p class="mt-1 text-sm text-gray-600">{{ __('A primeira imagem é a principal. JPG, PNG ou WEBP, até 2 MB.') }}</p>
    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @forelse ($product->images as $image)
            <div class="rounded-lg border border-gray-200 p-2">
                <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text }}" class="aspect-square w-full rounded object-cover">
                <label class="mt-3 flex items-center gap-2 text-sm text-red-700">
                    <input type="checkbox" name="removed_images[]" value="{{ $image->id }}" @checked(in_array($image->id, old('removed_images', []))) class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                    {{ __('Remover ao guardar') }}
                </label>
            </div>
        @empty
            <p class="col-span-full text-sm text-gray-500">{{ __('Este produto ainda não tem imagens.') }}</p>
        @endforelse
    </div>
    <div class="mt-6 grid gap-4 border-t border-gray-200 pt-6 sm:grid-cols-2">
        <div>
            <x-input-label for="image" :value="__('Adicionar imagem (opcional)')" />
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-gray-700">
            <x-input-error :messages="$errors->get('image')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="alt_text" :value="__('Descrição da imagem (opcional)')" />
            <x-text-input id="alt_text" name="alt_text" type="text" maxlength="150" class="mt-1 block w-full" :value="old('alt_text')" />
            <x-input-error :messages="$errors->get('alt_text')" class="mt-2" />
        </div>
    </div>
</section>
