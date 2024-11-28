<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProposalRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data_chegada' => ['required', 'date', 'after_or_equal:data_saida'],
            'data_saida' => ['required', 'date', 'before_or_equal:data_chegada'],
            'preco' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Mensagens de erro personalizadas.
     */
    public function messages(): array
    {
        return [
            'data_chegada.required' => 'A data de chegada é obrigatória.',
            'data_chegada.date' => 'A data de chegada deve ser uma data válida.',
            'data_chegada.after_or_equal' => 'A data de chegada deve ser igual ou posterior à data de saída.',
            'data_saida.required' => 'A data de saída é obrigatória.',
            'data_saida.date' => 'A data de saída deve ser uma data válida.',
            'data_saida.before_or_equal' => 'A data de saída deve ser igual ou anterior à data de chegada.',
            'preco.required' => 'O preço é obrigatório.',
            'preco.numeric' => 'O preço deve ser um valor numérico.',
            'preco.min' => 'O preço deve ser um valor positivo.',
        ];
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
