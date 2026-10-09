<div class="bg-white p-6 shadow-sm sm:rounded-lg">
    <h3 class="text-lg font-medium text-gray-900">{{ __('Variantes (tamanho e cor)') }}</h3>
    <p class="mt-1 text-sm text-gray-600">{{ __('Cada combinação de tamanho e cor tem o seu stock. O preço próprio é opcional: se ficar vazio, usa o preço do produto.') }}</p>

    @if ($errors->variant->any())
        <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
            <ul class="list-disc ps-5">
                @foreach ($errors->variant->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach (['Tamanho', 'Cor', 'SKU', 'Stock', 'Preço próprio (€)', ''] as $heading)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __($heading) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($product->variants as $variant)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $variant->size }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $variant->color }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $variant->sku }}</td>
                        <td class="px-4 py-3">
                            <input type="number" name="stock" min="0" required form="variant-{{ $variant->id }}" value="{{ $variant->stock }}" class="w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" name="variant_price" step="0.01" min="0" form="variant-{{ $variant->id }}" value="{{ $variant->price_cents !== null ? number_format($variant->price_cents / 100, 2, '.', '') : '' }}" placeholder="{{ number_format($product->price_cents / 100, 2, '.', '') }}" class="w-28 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <form id="variant-{{ $variant->id }}" method="POST" action="{{ route('admin.variants.update', $variant) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900">{{ __('Guardar') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.variants.destroy', $variant) }}" class="ms-3 inline" onsubmit="return confirm('{{ __('Eliminar esta variante?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Eliminar') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('Este produto ainda não tem variantes.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="mt-6 flex flex-wrap items-end gap-3 border-t border-gray-200 pt-6">
        @csrf
        <div>
            <x-input-label for="size" :value="__('Tamanho')" />
            <x-text-input id="size" name="size" type="text" class="mt-1 block w-24" :value="old('size')" placeholder="M" required />
        </div>
        <div>
            <x-input-label for="color" :value="__('Cor')" />
            <x-text-input id="color" name="color" type="text" class="mt-1 block w-32" :value="old('color')" placeholder="Azul" required />
        </div>
        <div>
            <x-input-label for="stock" :value="__('Stock')" />
            <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-24" :value="old('stock', 0)" required />
        </div>
        <div>
            <x-input-label for="variant_price" :value="__('Preço próprio (€)')" />
            <x-text-input id="variant_price" name="variant_price" type="number" step="0.01" min="0" class="mt-1 block w-28" :value="old('variant_price')" />
        </div>
        <x-primary-button>{{ __('Adicionar variante') }}</x-primary-button>
    </form>
</div>