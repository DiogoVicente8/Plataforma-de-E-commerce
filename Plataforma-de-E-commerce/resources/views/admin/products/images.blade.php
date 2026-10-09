<div class="bg-white p-6 shadow-sm sm:rounded-lg">
    <h3 class="text-lg font-medium text-gray-900">{{ __('Imagens') }}</h3>
    <p class="mt-1 text-sm text-gray-600">{{ __('A primeira imagem é a principal. JPG, PNG ou WEBP, até 2 MB.') }}</p>

    @if ($errors->image->any())
        <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
            <ul class="list-disc ps-5">
                @foreach ($errors->image->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @forelse ($product->images as $image)
            <div class="rounded-md border border-gray-200 p-2">
                <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text }}" class="aspect-square w-full rounded object-cover">
                <form method="POST" action="{{ route('admin.images.destroy', $image) }}" class="mt-2 text-center" onsubmit="return confirm('{{ __('Eliminar esta imagem?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-900">{{ __('Eliminar') }}</button>
                </form>
            </div>
        @empty
            <p class="col-span-full text-sm text-gray-500">{{ __('Este produto ainda não tem imagens.') }}</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-6 flex flex-wrap items-end gap-3 border-t border-gray-200 pt-6">
        @csrf
        <div>
            <x-input-label for="image" :value="__('Ficheiro')" />
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-1 block text-sm text-gray-700">
        </div>
        <div>
            <x-input-label for="alt_text" :value="__('Descrição da imagem (opcional)')" />
            <x-text-input id="alt_text" name="alt_text" type="text" class="mt-1 block w-64" :value="old('alt_text')" />
        </div>
        <x-primary-button>{{ __('Carregar imagem') }}</x-primary-button>
    </form>
</div>