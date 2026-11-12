<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => str_replace(' ', '', (string) $this->input('phone')),
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone_country' => ['required', Rule::in(array_keys(config('countries')))],
            // Números portugueses: telemóvel (91, 92, 93, 96) ou fixo (2…). Estrangeiros: 6 a 14 dígitos.
            'phone' => ['required', $this->input('phone_country') === 'PT'
                ? 'regex:/^(9[1236]\d{7}|2\d{8})$/'
                : 'regex:/^\d{6,14}$/'],
            'street' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'regex:/^\d{4}-\d{3}$/'],
            'city' => ['required', 'string', 'max:100'],
            'is_default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => __('Indica o nome de quem recebe a encomenda.'),
            'phone_country.in' => __('Escolhe um país da lista.'),
            'phone.required' => __('O telefone é obrigatório.'),
            'phone.regex' => __('Indica um número de telefone válido para o país escolhido.'),
            'street.required' => __('A morada é obrigatória.'),
            'postal_code.required' => __('O código postal é obrigatório.'),
            'postal_code.regex' => __('O código postal tem de ter o formato 0000-000.'),
            'city.required' => __('A localidade é obrigatória.'),
        ];
    }
}
