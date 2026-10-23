<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // o catálogo é público
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:40'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['name', 'price_asc', 'price_desc', 'newest'])],
        ];
    }
        public function messages(): array
    {
        return [
            'max_price.gte' => __('O preço máximo tem de ser maior ou igual ao mínimo.'),
            'sort.in' => __('Ordenação inválida.'),
        ];
    }
}