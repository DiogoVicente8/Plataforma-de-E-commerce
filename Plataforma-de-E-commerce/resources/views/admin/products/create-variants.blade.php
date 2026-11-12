@php
    $initialVariants = $product->exists ? $product->variants->map(fn ($variant) => [
        'id' => $variant->id, 'size' => $variant->size, 'color' => $variant->color,
        'stock' => $variant->stock,
        'variant_price' => $variant->price_cents === null ? '' : number_format($variant->price_cents / 100, 2, '.', ''),
    ])->all() : [];
    $formVariants = session()->hasOldInput('variants_present')
        ? array_values(old('variants', []) ?? [])
        : array_values(old('variants', $initialVariants) ?? []);
@endphp
<section class="rounded-xl border border-gray-200 p-5" x-data="{
    variants: @js($formVariants).map((variant, index) => ({ ...variant, rowId: index })),
    nextId: {{ count($formVariants) }},
    removed: @js(old('removed_variants', []))
}">
    <h3 class="text-lg font-medium text-gray-900">{{ __('Variantes (tamanho e cor)') }}</h3>
    <p class="mt-1 text-sm text-gray-600">{{ __('Adicione uma linha por tamanho e cor, com o respetivo stock. O preço próprio é opcional; vazio, usa o preço do produto.') }}</p>
    <p class="mt-2 text-sm text-gray-500">{{ __('Sem variantes com stock, o produto aparece como esgotado na loja.') }}</p>

    @foreach ($errors->getMessages() as $field => $messages)
        @if ($field === 'variants' || str_starts_with($field, 'variants.'))
            <x-input-error :messages="$messages" class="mt-2" />
        @endif
    @endforeach

    <input type="hidden" name="variants_present" value="1">
    <template x-for="id in removed" :key="id"><input type="hidden" name="removed_variants[]" :value="id"></template>
    <datalist id="product-sizes">
        @foreach (\App\Models\ProductVariant::SIZES as $size)
            <option value="{{ $size }}"></option>
        @endforeach
    </datalist>

    <div class="mt-5 space-y-4">
        <template x-for="(variant, index) in variants" :key="variant.rowId">
            <div class="grid gap-4 rounded-lg bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                <input x-bind:disabled="!variant.id" type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id">
                <div>
                    <label :for="'variant-size-' + variant.rowId" class="block text-sm font-medium text-gray-700">{{ __('Tamanho') }}</label>
                    <input :id="'variant-size-' + variant.rowId" :name="'variants[' + index + '][size]'" x-model="variant.size" :readonly="!!variant.id" type="text" list="product-sizes" maxlength="10" placeholder="M" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label :for="'variant-color-' + variant.rowId" class="block text-sm font-medium text-gray-700">{{ __('Cor') }}</label>
                    <input :id="'variant-color-' + variant.rowId" :name="'variants[' + index + '][color]'" x-model="variant.color" :readonly="!!variant.id" type="text" maxlength="40" placeholder="Azul" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label :for="'variant-stock-' + variant.rowId" class="block text-sm font-medium text-gray-700">{{ __('Stock') }}</label>
                    <input :id="'variant-stock-' + variant.rowId" :name="'variants[' + index + '][stock]'" x-model="variant.stock" type="number" min="0" max="100000" step="1" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label :for="'variant-price-' + variant.rowId" class="block text-sm font-medium text-gray-700">{{ __('Preço próprio (€)') }}</label>
                    <input :id="'variant-price-' + variant.rowId" :name="'variants[' + index + '][variant_price]'" x-model="variant.variant_price" type="number" min="0" max="99999" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <button type="button" @click="if (variant.id) removed.push(variant.id); variants.splice(index, 1)" :aria-label="'{{ __('Remover variante') }} ' + (index + 1)" class="self-end rounded-full border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:border-gray-900 hover:text-gray-900">{{ __('Remover') }}</button>
            </div>
        </template>
    </div>
    <button type="button" @click="variants.push({ rowId: nextId++, size: '', color: '', stock: 0, variant_price: '' })" :disabled="variants.length >= 200" class="mt-5 rounded-full bg-gray-900 px-5 py-2 text-sm text-white hover:bg-gray-700 disabled:opacity-50">{{ __('Adicionar variante') }}</button>
    <noscript><p class="mt-2 text-sm text-gray-600">{{ __('Ative o JavaScript para gerir as variantes nesta página.') }}</p></noscript>
</section>

