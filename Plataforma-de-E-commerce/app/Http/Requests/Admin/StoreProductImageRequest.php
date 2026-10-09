<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    /** Erros separados dos outros formulários da mesma página. */
    protected $errorBag = 'image';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => __('Escolhe uma imagem.'),
            'image.image' => __('O ficheiro tem de ser uma imagem.'),
            'image.mimes' => __('A imagem tem de ser JPG, PNG ou WEBP.'),
            'image.max' => __('A imagem não pode ter mais de 2 MB.'),
        ];
    }
}