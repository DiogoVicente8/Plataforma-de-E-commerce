<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    /** Erros separados dos do formulário do produto (estão na mesma página). */
    protected $errorBag = 'variant';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'size' => Str::upper(trim((string) $this->input('size'))),
            'color' => Str::ucfirst(Str::lower(trim((string) $this->input('color')))),
        ]);
    }

    public function rules(): array
    {
        return [
            'size' => [
                'required', 'string', 'max:10',
                Rule::unique('product_variants', 'size')
                    ->where('product_id', $this->route('product')->id)
                    ->where('color', $this->input('color')),
            ],
            'color' => ['required', 'string', 'max:40'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'variant_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ];
    }

    public function messages(): array
    {
        return [
            'size.required' => __('O tamanho é obrigatório.'),
            'size.unique' => __('Este produto já tem uma variante com este tamanho e esta cor.'),
            'color.required' => __('A cor é obrigatória.'),
            'stock.required' => __('O stock é obrigatório.'),
            'stock.min' => __('O stock não pode ser negativo.'),
            'variant_price.numeric' => __('O preço tem de ser um número.'),
        ];
    }
}