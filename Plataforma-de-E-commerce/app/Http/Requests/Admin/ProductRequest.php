<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
        ]);

        if (is_array($this->input('variants'))) {
            $this->merge(['variants' => array_map(function ($variant) {
                if (! is_array($variant) || ! empty($variant['id'])) {
                    return $variant;
                }
                if (is_string($variant['size'] ?? null)) {
                    $variant['size'] = Str::upper(trim($variant['size']));
                }
                if (is_string($variant['color'] ?? null)) {
                    $variant['color'] = Str::ucfirst(Str::lower(trim($variant['color'])));
                }

                return $variant;
            }, $this->input('variants'))]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:170', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:150'],
            'variants' => ['nullable', 'array', 'max:200'],
            'variants.*' => ['array:id,size,color,stock,variant_price'],
            'variants.*.id' => ['nullable', $this->route('product') ? 'integer' : 'prohibited', 'distinct', Rule::exists('product_variants', 'id')->where('product_id', $this->route('product')?->id)],
            'removed_variants' => ['nullable', 'array', 'max:200'],
            'removed_variants.*' => ['integer', 'distinct', Rule::exists('product_variants', 'id')->where('product_id', $this->route('product')?->id)],
            'removed_images' => ['nullable', 'array', 'max:200'],
            'removed_images.*' => ['integer', 'distinct', Rule::exists('product_images', 'id')->where('product_id', $this->route('product')?->id)],
            'variants.*.size' => ['required', 'string', 'max:10'],
            'variants.*.color' => ['required', 'string', 'max:40'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'variants.*.variant_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $combinations = [];
            $product = $this->route('product');
            $submittedIds = collect($this->input('variants', []) ?? [])->pluck('id')->filter()->all();
            $removedIds = $this->input('removed_variants', []) ?? [];
            if ($product) {
                foreach ($product->variants()->whereNotIn('id', array_merge($submittedIds, $removedIds))->get() as $existing) {
                    $combinations[json_encode([Str::upper($existing->size), Str::lower($existing->color)])] = true;
                }
            }
            foreach ($this->input('variants', []) ?? [] as $index => $variant) {
                if (! empty($variant['id']) && in_array($variant['id'], $removedIds)) {
                    $validator->errors()->add("variants.$index.id", __('Uma variante removida não pode ser atualizada.'));
                }
                if (! empty($variant['id'])) {
                    $existing = $product->variants()->find($variant['id']);
                    if ($existing->size !== $variant['size'] || $existing->color !== $variant['color']) {
                        $validator->errors()->add("variants.$index.size", __('Para mudar o tamanho ou a cor, remova a variante e adicione outra.'));
                    }
                }
                $key = json_encode([Str::upper($variant['size']), Str::lower($variant['color'])]);
                if (isset($combinations[$key])) {
                    $validator->errors()->add("variants.$index.size", __('Esta combinação de tamanho e cor está repetida.'));
                }
                $combinations[$key] = true;
            }
        }];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('Escolhe uma categoria.'),
            'category_id.exists' => __('A categoria escolhida não existe.'),
            'name.required' => __('O nome do produto é obrigatório.'),
            'slug.required' => __('O nome do produto é obrigatório.'),
            'slug.unique' => __('Já existe um produto com este slug.'),
            'price.required' => __('O preço é obrigatório.'),
            'price.numeric' => __('O preço tem de ser um número.'),
            'price.min' => __('O preço não pode ser negativo.'),
            'image.image' => __('O ficheiro tem de ser uma imagem.'),
            'image.mimes' => __('A imagem tem de ser JPG, PNG ou WEBP.'),
            'image.max' => __('A imagem não pode ter mais de 2 MB.'),
            'variants.max' => __('Pode guardar até 200 variantes de cada vez.'),
            'variants.*.size.required' => __('O tamanho é obrigatório.'),
            'variants.*.color.required' => __('A cor é obrigatória.'),
            'variants.*.stock.required' => __('O stock é obrigatório.'),
            'variants.*.stock.integer' => __('O stock tem de ser um número inteiro.'),
            'variants.*.stock.min' => __('O stock não pode ser negativo.'),
            'variants.*.variant_price.numeric' => __('O preço da variante tem de ser um número.'),
        ];
    }

    /** Dados prontos a gravar: converte o preço de euros para cêntimos. */
    public function productData(): array
    {
        $data = $this->safe()->only(['category_id', 'name', 'slug', 'description']);
        $data['price_cents'] = (int) round((float) $this->validated('price') * 100);
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
