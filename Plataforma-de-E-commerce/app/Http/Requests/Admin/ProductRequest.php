<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        ];
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
        ];
    }

    /** Dados prontos a gravar: converte o preço de euros para cêntimos. */
    public function productData(): array
    {
        $data = $this->safe()->except(['price', 'is_active']);
        $data['price_cents'] = (int) round((float) $this->validated('price') * 100);
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}